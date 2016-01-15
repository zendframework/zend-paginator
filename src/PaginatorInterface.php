<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Countable;
use Traversable;
use IteratorAggregate;
use Zend\Paginator\Adapter\AdapterInterface;

interface PaginatorInterface extends Countable, IteratorAggregate
{

    /**
     * Returns the number of pages.
     *
     * @return int
     */
    public function count();

    /**
     * Returns the total number of items available.
     *
     * @return int
     */
    public function getTotalItemCount();

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param  int $relativeItemNumber Relative item number
     * @param  int $pageNumber Page number
     * @return int
     */
    public function getAbsoluteItemNumber($relativeItemNumber, $pageNumber = null);

    /**
     * Returns the adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter();

    /**
     * Returns the number of items for the current page.
     *
     * @return int
     */
    public function getCurrentItemCount();

    /**
     * Returns the items for the current page.
     *
     * @return Traversable
     */
    public function getCurrentItems();

    /**
     * Returns the current page number.
     *
     * @return int
     */
    public function getCurrentPageNumber();

    /**
     * Sets the current page number.
     *
     * @param  int $pageNumber Page number
     * @return Paginator $this
     */
    public function setCurrentPageNumber($pageNumber);

    /**
     * Returns an item from a page.  The current page is used if there's no
     * page specified.
     *
     * @param  int $itemNumber Item number (1 to itemCountPerPage)
     * @param  int $pageNumber
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getItem($itemNumber, $pageNumber = null);

    /**
     * Returns the number of items per page.
     *
     * @return int
     */
    public function getItemCountPerPage();

    /**
     * Sets the number of items per page.
     *
     * @param  int $itemCountPerPage
     * @return Paginator $this
     */
    public function setItemCountPerPage($itemCountPerPage = -1);

    /**
     * Returns the number of items in a collection.
     *
     * @param  mixed $items Items
     * @return int
     */
    public function getItemCount($items);

    /**
     * Returns the items for a given page.
     *
     * @param int $pageNumber
     * @return mixed
     */
    public function getItemsByPage($pageNumber);

    /**
     * Returns a foreach-compatible iterator.
     *
     * @throws Exception\RuntimeException
     * @return Traversable
     */
    public function getIterator();

    /**
     * Returns the page range (see property declaration above).
     *
     * @return int
     */
    public function getPageRange();

    /**
     * Sets the page range (see property declaration above).
     *
     * @param  int $pageRange
     * @return Paginator $this
     */
    public function setPageRange($pageRange);

    /**
     * Returns the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return \stdClass
     */
    public function getPages($scrollingStyle = null);

    /**
     * Returns a subset of pages within a given range.
     *
     * @param  int $lowerBound Lower bound of the range
     * @param  int $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound);

    /**
     * Brings the item number in range of the page.
     *
     * @param  int $itemNumber
     * @return int
     */
    public function normalizeItemNumber($itemNumber);

    /**
     * Brings the page number in range of the paginator.
     *
     * @param  int $pageNumber
     * @return int
     */
    public function normalizePageNumber($pageNumber);
}