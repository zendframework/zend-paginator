<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator;

use PHPUnit\Framework\TestCase;
use Zend\Paginator;
use Zend\Paginator\Adapter;
use ZendTest\Paginator\TestAsset\TestArrayAggregate;

/**
 * @group      Zend_Paginator
 * @covers  Zend\Paginator\Factory<extended>
 */
class FactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockSelect;

    protected $mockAdapter;

    protected function setUp()
    {
        $this->mockSelect = $this->createMock('Zend\Db\Sql\Select');

        $mockStatement = $this->createMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockResult = $this->createMock('Zend\Db\Adapter\Driver\ResultInterface');

        $mockDriver = $this->createMock('Zend\Db\Adapter\Driver\DriverInterface');
        $mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($mockResult));
        $mockPlatform = $this->createMock('Zend\Db\Adapter\Platform\PlatformInterface');
        $mockPlatform->expects($this->any())->method('getName')->will($this->returnValue('platform'));

        $this->mockAdapter = $this->getMockForAbstractClass(
            'Zend\Db\Adapter\Adapter',
            [$mockDriver, $mockPlatform]
        );
    }

    public function testCanFactoryPaginatorWithStringAdapterObject()
    {
        $datas = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, new Adapter\ArrayAdapter($datas));
        $this->assertInstanceOf('Zend\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterName()
    {
        $datas = [1, 2, 3];
        $paginator = Paginator\Factory::factory($datas, 'array');
        $this->assertInstanceOf('Zend\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
        $this->assertEquals(count($datas), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithStringAdapterAggregate()
    {
        $paginator = Paginator\Factory::factory(null, new TestArrayAggregate);
        $this->assertInstanceOf('Zend\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithDbSelect()
    {
        $paginator = Paginator\Factory::factory([$this->mockSelect, $this->mockAdapter], 'dbselect');
        $this->assertInstanceOf('Zend\Paginator\Adapter\DbSelect', $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneParameterWithArrayAdapter()
    {
        $datas = [
            'items' => [1, 2, 3],
            'adapter' => 'array',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf('Zend\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
        $this->assertEquals(count($datas['items']), $paginator->getCurrentItemCount());
    }

    public function testCanFactoryPaginatorWithOneParameterWithDbAdapter()
    {
        $datas = [
            'items' => [$this->mockSelect, $this->mockAdapter],
            'adapter' => 'dbselect',
        ];
        $paginator = Paginator\Factory::factory($datas);
        $this->assertInstanceOf('Zend\Paginator\Adapter\DbSelect', $paginator->getAdapter());
    }

    public function testCanFactoryPaginatorWithOneBadParameter()
    {
        $datas = [
            [1, 2, 3],
            'array',
        ];
        $this->expectException('Zend\Paginator\Exception\InvalidArgumentException');
        $paginator = Paginator\Factory::factory($datas);
    }
}
