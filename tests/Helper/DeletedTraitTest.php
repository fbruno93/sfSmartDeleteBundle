<?php

namespace Bfy\SmartDeleteBundle\Tests\Helper;

use Bfy\SmartDeleteBundle\Helper\DeletedTrait;

use PHPUnit\Framework\TestCase;

class DeletedTraitTest extends TestCase
{
    public function testLoadFrom(): void
    {
        $classTraited = new class {
            use DeletedTrait;

            private $name;

            public function getName() { return $this->name; }
            public function setName($name) { $this->name = $name; }
        };

        $classicClass = new class {
            public function getName() { return 'name'; }
        };

        $classTraited->loadFrom($classicClass);

        $this->assertEquals($classTraited->getName(), $classicClass->getName());
    }
}
