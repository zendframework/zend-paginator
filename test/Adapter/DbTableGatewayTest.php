<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator\Adapter;

use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Adapter\DbTableGateway;

/**
 * @group Zend_Paginator
 * @covers  Zend\Paginator\Adapter\DbTableGateway<extended>
 */
class DbTableGatewayTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockStatement;

    /** @var DbTableGateway */
    protected $dbTableGateway;

    /** @var \Zend\Db\TableGateway\TableGateway */
    protected $mockTableGateway;

    public function setup()
    {
        $mockStatement = $this->createMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockDriver = $this->createMock('Zend\Db\Adapter\Driver\DriverInterface');
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

        $tableName = 'foobar';
        $mockTableGateway = $this->getMockForAbstractClass(
            'Zend\Db\TableGateway\TableGateway',
            [$tableName, $mockAdapter]
        );

        $this->mockStatement = $mockStatement;

        $this->mockTableGateway = $mockTableGateway;
    }

    public function testGetItems()
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockStatement
             ->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testCount()
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock('Zend\Db\Adapter\Driver\ResultInterface');
        $mockResult->expects($this->any())
                   ->method('current')
                   ->will($this->returnValue([DbSelect::ROW_COUNT_COLUMN_NAME => 10]));

        $this->mockStatement->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $count = $this->dbTableGateway->count();
        $this->assertEquals(10, $count);
    }

    public function testGetItemsWithWhereAndOrder()
    {
        $where = "foo = bar";
        $order = "foo";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order);

        $mockResult = $this->createMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockStatement
             ->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testGetItemsWithWhereAndOrderAndGroup()
    {
        $where = "foo = bar";
        $order = "foo";
        $group = "foo";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group);

        $mockResult = $this->createMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            // @codingStandardsIgnoreStart
            ->with($this->equalTo('SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" ORDER BY "foo" ASC LIMIT limit OFFSET offset'));
            // @codingStandardsIgnoreEnd
        $this->mockStatement
             ->expects($this->any())
             ->method('execute')
             ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testGetItemsWithWhereAndOrderAndGroupAndHaving()
    {
        $where  = "foo = bar";
        $order  = "foo";
        $group  = "foo";
        $having = "count(foo)>0";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group, $having);

        $mockResult = $this->createMock('Zend\Db\Adapter\Driver\ResultInterface');
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            // @codingStandardsIgnoreStart
            ->with($this->equalTo('SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" HAVING count(foo)>0 ORDER BY "foo" ASC LIMIT limit OFFSET offset'));
            // @codingStandardsIgnoreEnd
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }
}
