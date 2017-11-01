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
use Zend\Paginator\Adapter;

/**
 * @group      Zend_Paginator
 * @covers  Zend\Paginator\Adapter\ArrayAdapter<extended>
 */
class ArrayTest extends TestCase
{
    /**
     * @var Adapter\ArrayAdapter
     */
    private $adapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->adapter = new Adapter\ArrayAdapter(range(1, 101));
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testGetsItemsAtOffsetZero()
    {
        $expected = range(1, 10);
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testGetsItemsAtOffsetTen()
    {
        $expected = range(11, 20);
        $actual = $this->adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }

    public function testReturnsCorrectCount()
    {
        $this->assertEquals(101, $this->adapter->count());
    }


    /**
     * @group ZF-4151
     */
    public function testEmptySet()
    {
        $this->adapter = new Adapter\ArrayAdapter([]);
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }
}
