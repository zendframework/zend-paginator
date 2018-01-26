<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator;

use ArrayIterator;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Adapter\Driver as DbDriver;
use Zend\Db\Adapter\Platform;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter;
use Zend\Paginator\AdapterPluginManager;

/**
 * @group      Zend_Paginator
 * @covers  Zend\Paginator\AdapterPluginManager<extended>
 */
class AdapterPluginManagerTest extends TestCase
{
    /**
     * @var AdapterPluginManager
     */
    protected $adapterPluginManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
    */
    protected $mockSelect;

    protected $mockAdapter;

    protected function setUp()
    {
        $this->adapterPluginManager = new AdapterPluginManager(
            $this->getMockBuilder(ContainerInterface::class)->getMock()
        );
        $this->mockSelect = $this->createMock(Select::class);

        $mockStatement = $this->createMock(DbDriver\StatementInterface::class);
        $mockResult = $this->createMock(DbDriver\ResultInterface::class);

        $mockDriver = $this->createMock(DbDriver\DriverInterface::class);
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->createMock(Platform\PlatformInterface::class);
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockAdapter = $this->getMockForAbstractClass(
            DbAdapter::class,
            [$mockDriver, $mockPlatform]
        );
    }

    public function testCanRetrieveAdapterPlugin()
    {
        $plugin = $this->adapterPluginManager->get('array', [1, 2, 3]);
        $this->assertInstanceOf(Adapter\ArrayAdapter::class, $plugin);
        $plugin = $this->adapterPluginManager->get('iterator', [ new ArrayIterator(range(1, 101)) ]);
        $this->assertInstanceOf(Adapter\Iterator::class, $plugin);
        $plugin = $this->adapterPluginManager->get('dbselect', [$this->mockSelect, $this->mockAdapter]);
        $this->assertInstanceOf(Adapter\DbSelect::class, $plugin);
        $plugin = $this->adapterPluginManager->get('null', [ 101 ]);
        $this->assertInstanceOf(Adapter\NullFill::class, $plugin);

        // Test dbtablegateway
        $mockStatement = $this->createMock(DbDriver\StatementInterface::class);
        $mockDriver = $this->createMock(DbDriver\DriverInterface::class);
        $mockDriver->expects($this->any())
                   ->method('createStatement')
                   ->will($this->returnValue($mockStatement));
        $mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter = $this->getMockForAbstractClass(
            DbAdapter::class,
            [$mockDriver, new Platform\Sql92()]
        );
        $mockTableGateway = $this->getMockForAbstractClass(
            TableGateway::class,
            ['foobar', $mockAdapter]
        );
        $where  = "foo = bar";
        $order  = "foo";
        $group  = "foo";
        $having = "count(foo)>0";
        $plugin = $this->adapterPluginManager->get(
            'dbtablegateway',
            [$mockTableGateway, $where, $order, $group, $having]
        );
        $this->assertInstanceOf(Adapter\DbTableGateway::class, $plugin);

        // Test Callback
        $itemsCallback = function () {
            return [];
        };
        $countCallback = function () {
            return 0;
        };

        $plugin = $this->adapterPluginManager->get('callback', [$itemsCallback, $countCallback]);
        $this->assertInstanceOf(Adapter\Callback::class, $plugin);
    }

    public function testFactoryCreatedDbSelectCanUseCustomCountSelect()
    {
        $mockSelect      = $this->createMock(Select::class);
        $mockSelectCount = $this->createMock(Select::class);

        $mockResult    = $this->createMock(DbDriver\ResultInterface::class);
        $mockStatement = $this->createMock(DbDriver\StatementInterface::class);

        $mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $mockSql = $this->getMockBuilder(Sql::class)
            ->setMethods(['prepareStatementForSqlObject'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockSql
            ->expects($this->any())
            ->method('prepareStatementForSqlObject')
            ->with($mockSelectCount)
            ->will($this->returnValue($mockStatement));

        $mockResult
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue([Adapter\DbSelect::ROW_COUNT_COLUMN_NAME => 5]));

        $plugin = $this->adapterPluginManager->get(
            'dbselect',
            [$mockSelect, $mockSql, null, $mockSelectCount]
        );
        $count = $plugin->count();
        $this->assertEquals(5, $count);
    }
}
