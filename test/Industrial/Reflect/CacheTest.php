<?php

require_once "test/Autoload.php";

class Industrial_Reflect_CacheTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $size = \Industrial\Reflect\Cache::size();

        $className = "ReflectCacheTestA";
        $cached = \Industrial\Reflect\Cache::get($className);
        $this->assertTrue($cached instanceof \ReflectionClass);
        $this->assertEquals($className, $cached->name);
        $this->assertEquals($size + 1, \Industrial\Reflect\Cache::size());

        $className = "ReflectCacheTestA";
        $cached = \Industrial\Reflect\Cache::get($className);
        $this->assertTrue($cached instanceof \ReflectionClass);
        $this->assertEquals($className, $cached->name);

        $this->assertEquals($size + 1, \Industrial\Reflect\Cache::size());

        $className = "ReflectCacheTestB";
        $cached = \Industrial\Reflect\Cache::get($className);
        $this->assertTrue($cached instanceof \ReflectionClass);
        $this->assertEquals($className, $cached->name);
        $this->assertEquals($size + 2, \Industrial\Reflect\Cache::size());
    }
}

class ReflectCacheTestA {}
class ReflectCacheTestB {}
