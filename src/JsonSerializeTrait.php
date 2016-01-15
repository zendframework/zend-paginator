<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */


namespace Zend\Paginator;

use Zend\Db\ResultSet\AbstractResultSet;
use Zend\Json\Json;

trait JsonSerializeTrait
{

    /**
     * Serializes the object as a string.  Proxies to {@link toJson()}.
     *
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->toJson();
    }

    /**
     * Returns the items of the current page as JSON.
     *
     * @return string
     */
    public function toJson()
    {
        $currentItems = $this->getCurrentItems();

        if ($currentItems instanceof AbstractResultSet) {
            return Json::encode($currentItems->toArray());
        }
        return Json::encode($currentItems);
    }
}
