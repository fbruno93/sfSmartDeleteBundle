<?php

namespace Bfy\SmartDeleteBundle\Helper;

use ReflectionClass;
use ReflectionException;

trait DeletedTrait
{
    /**
     * Set attribute of the current object with object given
     *
     * @param object $row
     * @throws ReflectionException
     */
    public function loadFrom(object $row) {
        $r = new ReflectionClass($row);

        foreach ($r->getMethods() as $method) {
            if (substr($method->getName(), 0, 3) !== 'get') {
                continue;
            }

            $localMethod = str_replace('get', 'set', $method->getName());

            if (!method_exists($this, $localMethod)) {
                continue;
            }

            $rowMethodName = $method->getName();

            $this->$localMethod($row->$rowMethodName());
        }
    }
}