<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use ArrayIterator;
use Countable;
use Traversable;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;

class SimplePaginator implements PaginatorInterface
{
    /**
     * Adapter
     *
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * Number of items in the current page
     *
     * @var int
     */
    protected $currentItemCount = null;

    /**
     * Current page items
     *
     * @var Traversable
     */
    protected $currentItems = null;

    /**
     * Current page number (starting from 1)
     *
     * @var int
     */
    protected $currentPageNumber = 1;

    /**
     * Number of items per page
     *
     * @var int
     */
    protected $itemCountPerPage = 10;

    /**
     * Number of pages
     *
     * @var int
     */
    protected $pageCount = null;

    /**
     * Number of local pages (i.e., the number of discrete page numbers
     * that will be displayed, including the current page number)
     *
     * @var int
     */
    protected $pageRange = 10;

    /**
     * Pages
     *
     * @var \stdClass
     */
    protected $pages = null;

    /**
     * Constructor.
     *
     * @param AdapterInterface|AdapterAggregateInterface $adapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($adapter)
    {
        if ($adapter instanceof AdapterInterface) {
            $this->adapter = $adapter;
        } elseif ($adapter instanceof AdapterAggregateInterface) {
            $this->adapter = $adapter->getPaginatorAdapter();
        } else {
            throw new Exception\InvalidArgumentException(
                'Zend\Paginator only accepts instances of the type ' .
                'Zend\Paginator\Adapter\AdapterInterface or Zend\Paginator\AdapterAggregateInterface.'
            );
        }
    }

    /**
     * Returns the number of pages.
     *
     * @return int
     */
    public function count()
    {
        if (!$this->pageCount) {
            $this->pageCount = $this->_calculatePageCount();
        }

        return $this->pageCount;
    }

    /**
     * Returns the total number of items available.
     *
     * @return int
     */
    public function getTotalItemCount()
    {
        return count($this->getAdapter());
    }

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param  int $relativeItemNumber Relative item number
     * @param  int $pageNumber Page number
     * @return int
     */
    public function getAbsoluteItemNumber($relativeItemNumber, $pageNumber = null)
    {
        $relativeItemNumber = $this->normalizeItemNumber($relativeItemNumber);

        if ($pageNumber === null) {
            $pageNumber = $this->getCurrentPageNumber();
        }

        $pageNumber = $this->normalizePageNumber($pageNumber);

        return (($pageNumber - 1) * $this->getItemCountPerPage()) + $relativeItemNumber;
    }

    /**
     * Returns the adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Returns the number of items for the current page.
     *
     * @return int
     */
    public function getCurrentItemCount()
    {
        if ($this->currentItemCount === null) {
            $this->currentItemCount = $this->getItemCount($this->getCurrentItems());
        }

        return $this->currentItemCount;
    }

    /**
     * Returns the items for the current page.
     *
     * @return Traversable
     */
    public function getCurrentItems()
    {
        if ($this->currentItems === null) {
            $this->currentItems = $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->currentItems;
    }

    /**
     * Returns the current page number.
     *
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->normalizePageNumber($this->currentPageNumber);
    }

    /**
     * Sets the current page number.
     *
     * @param  int $pageNumber Page number
     * @return Paginator $this
     */
    public function setCurrentPageNumber($pageNumber)
    {
        $this->currentPageNumber = (int) $pageNumber;
        $this->currentItems      = null;
        $this->currentItemCount  = null;

        return $this;
    }

    /**
     * Returns an item from a page.  The current page is used if there's no
     * page specified.
     *
     * @param  int $itemNumber Item number (1 to itemCountPerPage)
     * @param  int $pageNumber
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getItem($itemNumber, $pageNumber = null)
    {
        if ($pageNumber === null) {
            $pageNumber = $this->getCurrentPageNumber();
        } elseif ($pageNumber < 0) {
            $pageNumber = ($this->count() + 1) + $pageNumber;
        }

        $page = $this->getItemsByPage($pageNumber);
        $itemCount = $this->getItemCount($page);

        if ($itemCount == 0) {
            throw new Exception\InvalidArgumentException('Page ' . $pageNumber . ' does not exist');
        }

        if ($itemNumber < 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            throw new Exception\InvalidArgumentException(
                "Page {$pageNumber} does not contain item number {$itemNumber}"
            );
        }

        return $page[$itemNumber - 1];
    }

    /**
     * Returns the number of items per page.
     *
     * @return int
     */
    public function getItemCountPerPage()
    {
        return $this->itemCountPerPage;
    }

    /**
     * Sets the number of items per page.
     *
     * @param  int $itemCountPerPage
     * @return Paginator $this
     */
    public function setItemCountPerPage($itemCountPerPage = -1)
    {
        $this->itemCountPerPage = (int) $itemCountPerPage;
        if ($this->itemCountPerPage < 1) {
            $this->itemCountPerPage = $this->getTotalItemCount();
        }
        $this->pageCount        = $this->_calculatePageCount();
        $this->currentItems     = null;
        $this->currentItemCount = null;

        return $this;
    }

    /**
     * Returns the number of items in a collection.
     *
     * @param  mixed $items Items
     * @return int
     */
    public function getItemCount($items)
    {
        $itemCount = 0;

        if (is_array($items) || $items instanceof Countable) {
            $itemCount = count($items);
        } elseif ($items instanceof Traversable) { // $items is something like LimitIterator
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     * Returns the items for a given page.
     *
     * @param int $pageNumber
     * @return mixed
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->adapter->getItems($offset, $this->getItemCountPerPage());

        if (!$items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        return $items;
    }

    /**
     * Returns a foreach-compatible iterator.
     *
     * @throws Exception\RuntimeException
     * @return Traversable
     */
    public function getIterator()
    {
        try {
            return $this->getCurrentItems();
        } catch (\Exception $e) {
            throw new Exception\RuntimeException('Error producing an iterator', null, $e);
        }
    }

    /**
     * Returns the page range (see property declaration above).
     *
     * @return int
     */
    public function getPageRange()
    {
        return $this->pageRange;
    }

    /**
     * Sets the page range (see property declaration above).
     *
     * @param  int $pageRange
     * @return Paginator $this
     */
    public function setPageRange($pageRange)
    {
        $this->pageRange = (int) $pageRange;

        return $this;
    }

    /**
     * Returns the page collection.
     *
     * @param  ScrollingStyleInterface $scrollingStyle Scrolling style
     * @return \stdClass
     */
    public function getPages($scrollingStyle = null)
    {

        if ($this->pages === null) {
            $this->pages = $this->_createPages($scrollingStyle);
        }

        return $this->pages;
    }

    /**
     * Returns a subset of pages within a given range.
     *
     * @param  int $lowerBound Lower bound of the range
     * @param  int $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound)
    {
        $lowerBound = $this->normalizePageNumber($lowerBound);
        $upperBound = $this->normalizePageNumber($upperBound);

        $pages = [];

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = $pageNumber;
        }

        return $pages;
    }

    /**
     * Brings the item number in range of the page.
     *
     * @param  int $itemNumber
     * @return int
     */
    public function normalizeItemNumber($itemNumber)
    {
        $itemNumber = (int) $itemNumber;

        if ($itemNumber < 1) {
            $itemNumber = 1;
        }

        if ($itemNumber > $this->getItemCountPerPage()) {
            $itemNumber = $this->getItemCountPerPage();
        }

        return $itemNumber;
    }

    /**
     * Brings the page number in range of the paginator.
     *
     * @param  int $pageNumber
     * @return int
     */
    public function normalizePageNumber($pageNumber)
    {
        $pageNumber = (int) $pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->count();

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * Calculates the page count.
     *
     * @return int
     */
    protected function _calculatePageCount()
    {
        return (int) ceil($this->getAdapter()->count() / $this->getItemCountPerPage());
    }

    /**
     * Creates the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return \stdClass
     */
    protected function _createPages($scrollingStyle = null)
    {
        $pageCount         = $this->count();
        $currentPageNumber = $this->getCurrentPageNumber();

        $pages = new \stdClass();
        $pages->pageCount        = $pageCount;
        $pages->itemCountPerPage = $this->getItemCountPerPage();
        $pages->first            = 1;
        $pages->current          = $currentPageNumber;
        $pages->last             = $pageCount;

        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $pages->previous = $currentPageNumber - 1;
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $pages->next = $currentPageNumber + 1;
        }

        // Pages in range
        $scrollingStyle = $this->_loadScrollingStyle($scrollingStyle);
        $pages->pagesInRange     = $scrollingStyle->getPages($this);
        $pages->firstPageInRange = min($pages->pagesInRange);
        $pages->lastPageInRange  = max($pages->pagesInRange);

        // Item numbers
        if ($this->getCurrentItems() !== null) {
            $pages->currentItemCount = $this->getCurrentItemCount();
            $pages->totalItemCount   = $this->getTotalItemCount();
            $pages->firstItemNumber  = $pages->totalItemCount
                ? (($currentPageNumber - 1) * $pages->itemCountPerPage) + 1
                : 0;
            $pages->lastItemNumber   = $pages->totalItemCount
                ? $pages->firstItemNumber + $pages->currentItemCount - 1
                : 0;
        }

        return $pages;
    }

    /**
     * Loads a scrolling style.
     *
     * @param string $scrollingStyle
     * @return ScrollingStyleInterface
     * @throws Exception\InvalidArgumentException
     */
    protected function _loadScrollingStyle($scrollingStyle = null)
    {
        switch (strtolower(gettype($scrollingStyle))) {
            case 'object':
                if (!$scrollingStyle instanceof ScrollingStyleInterface) {
                    throw new Exception\InvalidArgumentException(
                        'Scrolling style must implement Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                    );
                }

                return $scrollingStyle;

            case 'null':
                // Fall through to default case

            default:
                throw new Exception\InvalidArgumentException(
                    'Scrolling style must be a class ' .
                    'name or object implementing Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                );
        }
    }
}
