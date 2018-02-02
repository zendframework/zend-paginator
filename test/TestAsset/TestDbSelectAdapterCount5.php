<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Paginator\TestAsset;

class TestDbSelectAdapterCount5 extends \Zend\Paginator\Adapter\DbSelect
{
    public function count()
    {
        return 5;
    }

    public function getItems($pageNumber, $itemCountPerPage)
    {
        return new \ArrayObject(range(1, 5));
    }
}
