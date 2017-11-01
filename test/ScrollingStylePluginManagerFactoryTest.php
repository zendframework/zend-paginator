<?php
/**
 * @link      http://github.com/zendframework/zend-paginator for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Paginator\ScrollingStylePluginManager;
use Zend\Paginator\ScrollingStylePluginManagerFactory;
use Zend\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ScrollingStylePluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ScrollingStylePluginManagerFactory();

        $scrollingStyles = $factory($container, ScrollingStylePluginManager::class);
        $this->assertInstanceOf(ScrollingStylePluginManager::class, $scrollingStyles);

        if (method_exists($scrollingStyles, 'configure')) {
            // zend-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $scrollingStyles);
        } else {
            // zend-servicemanager v2
            $this->assertSame($container, $scrollingStyles->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $scrollingStyle = $this->prophesize(ScrollingStyleInterface::class)->reveal();

        $factory = new ScrollingStylePluginManagerFactory();
        $scrollingStyles = $factory($container, ScrollingStylePluginManager::class, [
            'services' => [
                'test' => $scrollingStyle,
            ],
        ]);
        $this->assertSame($scrollingStyle, $scrollingStyles->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $scrollingStyle = $this->prophesize(ScrollingStyleInterface::class)->reveal();

        $factory = new ScrollingStylePluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $scrollingStyle,
            ],
        ]);

        $scrollingStyles = $factory->createService($container->reveal());
        $this->assertSame($scrollingStyle, $scrollingStyles->get('test'));
    }
}
