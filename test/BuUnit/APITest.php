<?php

use Bu\Test\BuTest;
use Bu\API;

namespace Bu\BuUnit;

class APITest extends \Bu\Test\BuTest
{
    public static function CONFIG_CLASS()
    {
        return "\Bu\BuUnit\Config";
    }

    public function test_create_new_instance_of_api()
    {
        $api = \Bu\API::call("test");
        $this->assertNotNull($api);
        $this->assertInstanceOf("\Bu\API", $api);
    }

    public function test_get_class()
    {
        $CLASSKEY = $this->getRandomString();
        $api = \Bu\API::call("$CLASSKEY/add");
        $this->assertNotNull($api);
        $this->assertEquals($CLASSKEY, $api->getClassKey());
    }

    public function test_get_action()
    {
        $ACTION = "add";
        $api = \Bu\API::call("test/$ACTION");
        $this->assertNotNull($api);
        $this->assertEquals($ACTION, $api->getAction());
    }

    public function test_is_valid_action()
    {
        $this->assertTrue(\Bu\API::isValidAction(\Bu\API::ACTION_ADD()));
        $this->assertFalse(\Bu\API::isValidAction($this->getRandomString()));
    }

    public function test_is_valid_classkey()
    {
        $this->assertTrue(\Bu\Test\Sample\API::isValidClassKey("sample"));
        $this->assertFalse(\Bu\Test\Sample\API::isValidClassKey($this->getRandomString()));
    }

    public function test_is_valid_parameters()
    {
        $this->assertFalse(\Bu\Test\Sample\API::isValidParameters(""));
        $this->assertFalse(\Bu\Test\Sample\API::isValidParameters($this->getRandomString()));
        $this->assertTrue(\Bu\Test\Sample\API::isValidParameters("{}"));
    }

    public function assertAPIError($error, $method, $parameters = null)
    {
        $api = \Bu\API::call($method, $parameters);
        $this->assertTrue($api->hasErrors());
        $this->assertContains($error, $api->getErrors());
    }

    public function test_error_invalid_class()
    {
        $this->assertAPIError(\Bu\API::API_ERROR_INVALID_CLASSNAME(), $this->getRandomString() . "/add");
    }

    public function test_error_invalid_action()
    {
        $this->assertAPIError(\Bu\API::API_ERROR_INVALID_CLASSNAME(), $this->getRandomString() . "/" . $this->getRandomString());
    }

    public function test_error_invalid_parameters()
    {
        $this->assertAPIError(\Bu\API::API_ERROR_INVALID_PARAMETERS(), $this->getRandomString() . "/" . $this->getRandomString(), $this->getRandomString());
    }

    public function getSampleAPICall($method, $parameters, $sessionHash = null)
    {
        $api = \Bu\Test\Sample\API::call($method, json_encode($parameters), $sessionHash);
        $this->assertFalse($api->hasErrors());
        return $api;
    }

    public function test_execute_method_view()
    {
        $sample = $this->getNew("SampleClass");
        $api = $this->getSampleAPICall("sample/view", [ "sampleclass_id" => $sample->getValue("sampleclass_id") ]);
        $result = $api->execute();
        $this->assertNotNull($result);
        $this->assertEquals($result["sampleclass_id"], $sample->getValue("sampleclass_id"));
    }

    public function test_call_with_session_hash()
    {
        $session = $this->getNew("Session");
        $sample = $this->getNew("SampleClass");
        $api = $this->getSampleAPICall("sample/view", [ "sampleclass_id" => $sample->getValue("sampleclass_id") ], $session->getValue("hash"));
        $this->assertNotNull($api->getUser());
        $this->assertEquals($session->getUser()->getValue("user_id"), $api->getUser()->getValue("user_id"));
    }
    public function test_call_with_invalid_session()
    {
        $sample = $this->getNew("SampleClass");
        $api = $this->getSampleAPICall("sample/view", [ "sampleclass_id" => $sample->getValue("sampleclass_id") ], $this->getRandomString());
        $this->assertNull($api->getUser());
    }
}
