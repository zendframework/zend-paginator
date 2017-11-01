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
use Zend\Paginator;

/**
 * @group      Zend_Paginator
 * @covers  Zend\Paginator\Adapter\NullFill<extended>
 */
class NullFillTest extends TestCase
{
    /**
     * @var \Zend\Paginator\Adapter\NullFill
     */
    private $adapter;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->adapter = new Adapter\NullFill(101);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->adapter = null;
        parent::tearDown();
    }

    public function testGetsItems()
    {
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals(array_fill(0, 10, null), $actual);
    }

    public function testReturnsCorrectCount()
    {
        $this->assertEquals(101, $this->adapter->count());
    }

    /**
     * @group ZF-3873
     */
    public function testAdapterReturnsCorrectValues()
    {
        $paginator = new Paginator\Paginator(new Adapter\NullFill(2));
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(5);

        $pages = $paginator->getPages();

        $this->assertEquals(2, $pages->currentItemCount);
        $this->assertEquals(2, $pages->lastItemNumber);

        $paginator = new Paginator\Paginator(new Adapter\NullFill(19));
        $paginator->setCurrentPageNumber(4);
        $paginator->setItemCountPerPage(5);

        $pages = $paginator->getPages();

        $this->assertEquals(4, $pages->currentItemCount);
        $this->assertEquals(19, $pages->lastItemNumber);
    }

    /**
     * @group ZF-4151
     */
    public function testEmptySet()
    {
        $this->adapter = new Adapter\NullFill(0);
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals([], $actual);
    }

    /**
     * Verify that the fix for ZF-4151 doesn't create an OBO error
     */
    public function testSetOfOne()
    {
        $this->adapter = new Adapter\NullFill(1);
        $actual = $this->adapter->getItems(0, 10);
        $this->assertEquals(array_fill(0, 1, null), $actual);
    }
}
