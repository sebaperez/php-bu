<?php

namespace Bu\BuUnit;

use Bu\Test\Sample\SampleClass;
use Bu\Test\Sample\SampleClassMultiplePK;
use Bu\Test\BuTest;
use Bu\Base;

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
          "param" => 1
        ];
        $api = \Bu\API::get($method, $parameters);
        $this->assertNotNull($api);
        $this->assertEquals($method, $api->getMethod());
        $this->assertEquals($parameters, $api->getParameters());
    }

    public function assertAPIError($method, $parameters, $session = null, $expectedMessage = null)
    {
        $api = \Bu\API::get($method, $parameters, $session);
        $api->execute();
        $message = $api->getMessage();
        $this->assertEquals("error", $message["status"]);
        if ($expectedMessage) {
            if (isset($expectedMessage["errorCode"])) {
                $this->assertEquals($expectedMessage["errorCode"], $message["message"]["errorCode"]);
            }
        }
        return $api;
    }

    public function test_invalid_method_fails()
    {
        $this->assertAPIError($this->getRandomString(), [], null, [
          "errorCode" => \Bu\API::API_ERROR_INVALID_METHOD()
        ]);
    }
}
