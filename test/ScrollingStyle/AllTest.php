<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator\ScrollingStyle;

use PHPUnit\Framework\TestCase;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

/**
 * @group      Zend_Paginator
 * @covers  Zend\Paginator\ScrollingStyle\All<extended>
 */
class AllTest extends TestCase
{
    /**
     * @var \Zend\Paginator\ScrollingStyle\All
     */
    private $scrollingStyle = null;

    /**
     * @var \Zend\Paginator\Paginator
     */
    private $paginator = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->scrollingStyle = new \Zend\Paginator\ScrollingStyle\All();
        $this->paginator = new Paginator(new ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(10);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->scrollingStyle = null;
        $this->paginator = null;
        parent::tearDown();
    }

    public function testGetsPages()
    {
        $expected = array_combine(range(1, 11), range(1, 11));
        $pages = $this->scrollingStyle->getPages($this->paginator);
        $this->assertEquals($expected, $pages);
    }

    public function testGetsNextAndPreviousPageForFirstPage()
    {
        $this->paginator->setCurrentPageNumber(1);
        $pages = $this->paginator->getPages('All');

        $this->assertEquals(2, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondPage()
    {
        $this->paginator->setCurrentPageNumber(2);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(1, $pages->previous);
        $this->assertEquals(3, $pages->next);
    }

    public function testGetsNextAndPreviousPageForMiddlePage()
    {
        $this->paginator->setCurrentPageNumber(6);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(5, $pages->previous);
        $this->assertEquals(7, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondLastPage()
    {
        $this->paginator->setCurrentPageNumber(10);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(9, $pages->previous);
        $this->assertEquals(11, $pages->next);
    }

    public function testGetsNextAndPreviousPageForLastPage()
    {
        $this->paginator->setCurrentPageNumber(11);
        $pages = $this->paginator->getPages('All');
        $this->assertEquals(10, $pages->previous);
    }
}
