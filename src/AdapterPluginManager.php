<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for paginator adapters.
 *
 * Enforces that adapters retrieved are instances of
 * Adapter\AdapterInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default aliases
     *
     * Primarily for ensuring previously defined adapters select their
     * current counterparts.
     *
     * @var array
     */
    protected $aliases = [
        'dbselect'       => Adapter\DbSelect::class,
        'dbtablegateway' => Adapter\DbTableGateway::class,
        'null'           => Adapter\NullFill::class,
        'nullfill'       => Adapter\NullFill::class,
        'array'          => Adapter\ArrayAdapter::class,
        'iterator'       => Adapter\Iterator::class
    ];

    /**
     * Default set of adapter factories
     *
     * @var array
     */
    protected $factories = [
        Adapter\DbSelect::class       => Adapter\Service\DbSelectFactory::class,
        Adapter\DbTableGateway::class => Adapter\Service\DbTableGatewayFactory::class,
        Adapter\NullFill::class       => InvokableFactory::class,
        Adapter\Iterator::class       => Adapter\Service\IteratorFactory::class,
        Adapter\ArrayAdapter::class   => InvokableFactory::class
    ];

    protected $instanceOf = Adapter\AdapterInterface::class;
}
