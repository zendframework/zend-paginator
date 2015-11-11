<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator;

use Zend\Db\Adapter\Platform\Sql92;
use Zend\Paginator\AdapterPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

/**
 * @group      Zend_Paginator
 */
class AdapterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $adapaterPluginManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockSelect;

    protected $mockAdapter;

    protected function setUp()
    {
        $this->adapaterPluginManager = new AdapterPluginManager(
            $this->getMockBuilder('Interop\Container\ContainerInterface')->getMock()
        );
        $this->mockSelect = $this->getMock('Zend\Db\Sql\Select');

        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockResult = $this->getMock('Zend\Db\Adapter\Driver\ResultInterface');

        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->getMock('Zend\Db\Adapter\Platform\PlatformInterface');
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockAdapter = $this->getMockForAbstractClass(
            'Zend\Db\Adapter\Adapter',
            [$mockDriver, $mockPlatform]
        );
    }

    public function testCanRetrieveAdapterPlugin()
    {
        $plugin = $this->adapaterPluginManager->get('array', [1, 2, 3]);
        $this->assertInstanceOf('Zend\Paginator\Adapter\ArrayAdapter', $plugin);
        $plugin = $this->adapaterPluginManager->get('iterator', [ new \ArrayIterator(range(1, 101)) ]);
        $this->assertInstanceOf('Zend\Paginator\Adapter\Iterator', $plugin);
        $plugin = $this->adapaterPluginManager->get('dbselect', [$this->mockSelect, $this->mockAdapter]);
        $this->assertInstanceOf('Zend\Paginator\Adapter\DbSelect', $plugin);
        $plugin = $this->adapaterPluginManager->get('null', [ 101 ]);
        $this->assertInstanceOf('Zend\Paginator\Adapter\NullFill', $plugin);

        //test dbtablegateway
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())
                   ->method('createStatement')
                   ->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter = $this->getMockForAbstractClass(
            'Zend\Db\Adapter\Adapter',
            [$mockDriver, new Sql92()]
        );
        $mockTableGateway = $this->getMockForAbstractClass(
            'Zend\Db\TableGateway\TableGateway',
            ['foobar', $mockAdapter]
        );
        $where  = "foo = bar";
        $order  = "foo";
        $group  = "foo";
        $having = "count(foo)>0";
        $plugin = $this->adapaterPluginManager->get(
            'dbtablegateway',
            [$mockTableGateway, $where, $order, $group, $having]
        );
        $this->assertInstanceOf('Zend\Paginator\Adapter\DbTableGateway', $plugin);
    }

    public function testCanRetrievePluginManagerWithServiceManager()
    {
        $config = new ServiceManagerConfig([
            'factories' => [
                'PaginatorPluginManager'  => 'Zend\Mvc\Service\PaginatorPluginManagerFactory',
            ],
            'services' => [
                'Config' => []
            ]
        ]);
        $sm = $this->serviceManager = new ServiceManager($config->toArray());
        //$sm->setService('Config', []);
        $adapterPluginManager = $sm->get('PaginatorPluginManager');
        $this->assertInstanceOf('Zend\Paginator\AdapterPluginManager', $adapterPluginManager);
    }
}
