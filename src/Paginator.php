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
use Traversable;

class Paginator extends GlobalPaginator
{
    use JsonSerializeTrait,
        RenderTrait,
        FilterTrait,
        CachedTrait;

    /**
     * The cache tag prefix used to namespace Paginator results in the cache
     *
     */
    const CACHE_TAG_PREFIX = 'Zend_Paginator_';


    /**
     * @inheritdoc
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        if ($this->cacheEnabled()) {
            $data = static::$cache->getItem($this->_getCacheId($pageNumber));
            if ($data) {
                return $data;
            }
        }

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->adapter->getItems($offset, $this->getItemCountPerPage());

        $filter = $this->getFilter();

        if ($filter !== null) {
            $items = $filter->filter($items);
        }

        if (!$items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        if ($this->cacheEnabled()) {
            $cacheId = $this->_getCacheId($pageNumber);
            static::$cache->setItem($cacheId, $items);
        }

        return $items;
    }
}
