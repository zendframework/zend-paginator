<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-paginator for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Zend\Paginator\AdapterPluginManager;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Exception\RuntimeException;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class AdapterPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new AdapterPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return RuntimeException::class;
    }

    protected function getInstanceOf()
    {
        return AdapterInterface::class;
    }

    public function aliasProvider()
    {
        $pluginManager = $this->getPluginManager();
        $r = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            // Skipping as these have required arguments
            if (strpos($target, '\\Db')) {
                continue;
            }

            // Skipping as has required arguments
            if (strpos($target, '\\Callback')) {
                continue;
            }

            // Skipping as has required arguments
            if (strpos($target, '\\Iterator')) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }
}
