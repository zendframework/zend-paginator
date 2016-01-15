<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Paginator;

use Traversable;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;

class GlobalPaginator extends SimplePaginator implements GlobalSetupInterface
{

    /**
     * Adapter plugin manager
     *
     * @var AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * Configuration file
     *
     * @var array|null
     */
    protected static $config = null;

    /**
     * Default scrolling style
     *
     * @var string
     */
    protected static $defaultScrollingStyle = 'Sliding';

    /**
     * Default item count per page
     *
     * @var int
     */
    protected static $defaultItemCountPerPage = 10;

    /**
     * Number of items per page
     * Override parent's class default number.
     * When called getItemCountPerPage() method, $defaultItemCountPerPage will be used.
     *
     * @var int
     */
    protected $itemCountPerPage = null;

    /**
     * Scrolling style plugin manager
     *
     * @var ScrollingStylePluginManager
     */
    protected static $scrollingStyles = null;

    /**
     * Set a global config
     *
     * @param array|Traversable $config
     * @throws Exception\InvalidArgumentException
     */
    public static function setGlobalConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        static::$config = $config;

        if (isset($config['scrolling_style_plugins'])
            && null !== ($adapters = $config['scrolling_style_plugins'])
        ) {
            static::setScrollingStylePluginManager($adapters);
        }

        $scrollingStyle = isset($config['scrolling_style']) ? $config['scrolling_style'] : null;

        if ($scrollingStyle !== null) {
            static::setDefaultScrollingStyle($scrollingStyle);
        }
    }

    /**
     * Returns the default scrolling style.
     *
     * @return  string
     */
    public static function getDefaultScrollingStyle()
    {
        return static::$defaultScrollingStyle;
    }

    /**
     * Get the default item count per page
     *
     * @return int
     */
    public static function getDefaultItemCountPerPage()
    {
        return static::$defaultItemCountPerPage;
    }

    /**
     * Set the default item count per page
     *
     * @param int $count
     */
    public static function setDefaultItemCountPerPage($count)
    {
        static::$defaultItemCountPerPage = (int) $count;
    }

    /**
     * Sets the default scrolling style.
     *
     * @param  string $scrollingStyle
     */
    public static function setDefaultScrollingStyle($scrollingStyle = 'Sliding')
    {
        static::$defaultScrollingStyle = $scrollingStyle;
    }

    public static function setScrollingStylePluginManager($scrollingAdapters)
    {
        if (is_string($scrollingAdapters)) {
            if (!class_exists($scrollingAdapters)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate scrolling style plugin manager with class "%s"; class not found',
                    $scrollingAdapters
                ));
            }
            $scrollingAdapters = new $scrollingAdapters(new ServiceManager);
        }
        if (!$scrollingAdapters instanceof ScrollingStylePluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Pagination scrolling-style manager must extend ScrollingStylePluginManager; received "%s"',
                (is_object($scrollingAdapters) ? get_class($scrollingAdapters) : gettype($scrollingAdapters))
            ));
        }
        static::$scrollingStyles = $scrollingAdapters;
    }

    /**
     * Returns the scrolling style manager.  If it doesn't exist it's
     * created.
     *
     * @return ScrollingStylePluginManager
     */
    public static function getScrollingStylePluginManager()
    {
        if (static::$scrollingStyles === null) {
            static::$scrollingStyles = new ScrollingStylePluginManager(new ServiceManager);
        }

        return static::$scrollingStyles;
    }

    /**
     * Constructor.
     *
     * @param AdapterInterface|AdapterAggregateInterface $adapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($adapter)
    {
        parent::__construct($adapter);

        $config = static::$config;

        if (!empty($config)) {
            $setupMethods = ['ItemCountPerPage', 'PageRange'];

            foreach ($setupMethods as $setupMethod) {
                $key   = strtolower($setupMethod);
                $value = isset($config[$key]) ? $config[$key] : null;

                if ($value !== null) {
                    $setupMethod = 'set' . $setupMethod;
                    $this->$setupMethod($value);
                }
            }
        }
    }

    /**
     * Returns the number of items per page.
     *
     * @return int
     */
    public function getItemCountPerPage()
    {
        if (empty($this->itemCountPerPage)) {
            $this->itemCountPerPage = static::getDefaultItemCountPerPage();
        }

        return $this->itemCountPerPage;
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
        if ($scrollingStyle === null) {
            $scrollingStyle = static::$defaultScrollingStyle;
        }

        switch (strtolower(gettype($scrollingStyle))) {
            case 'object':
                if (!$scrollingStyle instanceof ScrollingStyleInterface) {
                    throw new Exception\InvalidArgumentException(
                        'Scrolling style must implement Zend\Paginator\ScrollingStyle\ScrollingStyleInterface'
                    );
                }

                return $scrollingStyle;

            case 'string':
                return static::getScrollingStylePluginManager()->get($scrollingStyle);

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