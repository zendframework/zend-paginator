<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Zend\Cache\Storage\IteratorInterface as CacheIterator;
use Zend\Cache\Storage\StorageInterface as CacheStorage;

trait CachedTrait
{

    /**
     * Enable or disable the cache by Zend\Paginator\Paginator instance
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Cache object
     *
     * @var CacheStorage
     */
    protected static $cache;

    /**
     * Sets a cache object
     *
     * @param CacheStorage $cache
     */
    public static function setCache(CacheStorage $cache)
    {
        static::$cache = $cache;
    }

    /**
     * Enables/Disables the cache for this instance
     *
     * @param bool $enable
     * @return Paginator
     */
    public function setCacheEnabled($enable)
    {
        $this->cacheEnabled = (bool) $enable;
        return $this;
    }

    /**
     * Returns the page item cache.
     *
     * @return array
     */
    public function getPageItemCache()
    {
        $data = [];
        if ($this->cacheEnabled()) {
            $prefixLength  = strlen(self::CACHE_TAG_PREFIX);
            $cacheIterator = static::$cache->getIterator();
            $cacheIterator->setMode(CacheIterator::CURRENT_AS_VALUE);
            foreach ($cacheIterator as $key => $value) {
                if (substr($key, 0, $prefixLength) == self::CACHE_TAG_PREFIX) {
                    $data[(int) substr($key, $prefixLength)] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Clear the page item cache.
     *
     * @param int $pageNumber
     * @return Paginator
     */
    public function clearPageItemCache($pageNumber = null)
    {
        if (!$this->cacheEnabled()) {
            return $this;
        }

        if (null === $pageNumber) {
            $prefixLength  = strlen(self::CACHE_TAG_PREFIX);
            $cacheIterator = static::$cache->getIterator();
            $cacheIterator->setMode(CacheIterator::CURRENT_AS_KEY);
            foreach ($cacheIterator as $key) {
                if (substr($key, 0, $prefixLength) == self::CACHE_TAG_PREFIX) {
                    static::$cache->removeItem($this->_getCacheId((int)substr($key, $prefixLength)));
                }
            }
        } else {
            $cleanId = $this->_getCacheId($pageNumber);
            static::$cache->removeItem($cleanId);
        }
        return $this;
    }

    /**
     * Tells if there is an active cache object
     * and if the cache has not been disabled
     *
     * @return bool
     */
    protected function cacheEnabled()
    {
        return ((static::$cache !== null) && $this->cacheEnabled);
    }

    /**
     * Makes an Id for the cache
     * Depends on the adapter object and the page number
     *
     * Used to store item in cache from that Paginator instance
     *  and that current page
     *
     * @param int $page
     * @return string
     */
    protected function _getCacheId($page = null)
    {
        if ($page === null) {
            $page = $this->getCurrentPageNumber();
        }
        return self::CACHE_TAG_PREFIX . $page . '_' . $this->_getCacheInternalId();
    }

    /**
     * Get the internal cache id
     * Depends on the adapter and the item count per page
     *
     * Used to tag that unique Paginator instance in cache
     *
     * @return string
     */
    protected function _getCacheInternalId()
    {
        return md5(serialize([
            spl_object_hash($this->getAdapter()),
            $this->getItemCountPerPage()
        ]));
    }
}
