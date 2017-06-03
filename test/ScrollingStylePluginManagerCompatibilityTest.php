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
use Zend\Paginator\ScrollingStylePluginManager;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Zend\Paginator\Exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class ScrollingStylePluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new ScrollingStylePluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return ScrollingStyleInterface::class;
    }
}
