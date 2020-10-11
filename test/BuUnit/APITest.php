<?php

use Bu\Test\Sample\SampleClass;
use Bu\Test\Sample\SampleClassMultiplePK;
use Bu\Test\BuTest;
use Bu\Base;

namespace Bu\BuUnit;

class APITest extends \Bu\Test\BuTest
{
    public static function CONFIG_CLASS()
    {
        return "\Bu\BuUnit\Config";
    }

    public function test_initiate_api()
    {
        $method = "test/api";
        $parameters = [
        "param1" => 1
      ];
        $api = \Bu\API::get($method, $parameters);
        $this->assertNotNull($api);
        $this->assertEquals($method, $api->getMethod());
        $this->assertEquals($parameters, $api->getParameters());
    }
}
