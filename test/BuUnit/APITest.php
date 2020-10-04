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

    public function getAPICall($method, $parameters, $sessionHash)
    {
        return \Bu\Test\Sample\API::call($method, json_encode($parameters, true), $sessionHash);
    }

    public function getAPIExecutionResponse($method, $parameters, $sessionHash)
    {
        $api = $this->getAPICall($method, $parameters, $sessionHash);
        return $api->execute();
    }

    public function assertAPIError($error, $method, $parameters = null, $sessionHash = null)
    {
        $result = $this->getAPIExecutionResponse($method, $parameters, $sessionHash);
        $this->assertIsArray($result);
        $this->assertEquals(\Bu\Test\Sample\API::API_STATUS_ERROR(), $result["status"]);
        $this->assertEquals($error, $result["data"]);
    }

    public function assertAPISuccess($method, $parameters = null, $sessionHash = null)
    {
        $result = $this->getAPIExecutionResponse($method, $parameters, $sessionHash);
        $this->assertIsArray($result);
        $this->assertEquals(\Bu\Test\Sample\API::API_STATUS_SUCCESS(), $result["status"]);
        return $result["data"];
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
        $this->assertAPIError(\Bu\API::API_ERROR_INVALID_PARAMETERS(), "sample/view", $this->getRandomString());
    }

    public function test_execute_method_view()
    {
        $session = $this->getNew("Session");
        $result = $this->assertAPISuccess("session/view", [ "session_id" => $session->getValue("session_id") ], $session->getValue("hash"));
        $this->assertEquals($result["session_id"], $session->getValue("session_id"));
    }

    public function test_execute_method_view_by_non_owner_user()
    {
        $session = $this->getNew("Session");
        $session2 = $this->getNew("Session");
        $this->assertAPIError(\Bu\API::API_ERROR_FORBIDDEN(), "session/view", [ "session_id" => $session->getValue("session_id") ], $session2->getValue("hash"));
    }

    public function test_call_with_session_hash()
    {
        $session = $this->getNew("Session");
        $sample = $this->getNew("SampleClass");
        $api = $this->getAPICall("sample/view", [ "sampleclass_id" => $sample->getValue("sampleclass_id") ], $session->getValue("hash"));
        $this->assertNotNull($api->getUser());
        $this->assertEquals($session->getUser()->getValue("user_id"), $api->getUser()->getValue("user_id"));
    }

    public function test_call_with_invalid_session()
    {
        $sample = $this->getNew("SampleClass");
        $api = $this->getAPICall("sample/view", [ "sampleclass_id" => $sample->getValue("sampleclass_id") ], $this->getRandomString());
        $this->assertNull($api->getUser());
    }

    public function test_get_object_owned_by_account()
    {
        $session = $this->getNew("Session");
        $user = $session->getUser();
        $result = $this->assertAPISuccess("user/view", [ "user_id" => $user->getValue("user_id") ], $session->getValue("hash"));
        $this->assertEquals($result["user_id"], $user->getValue("user_id"));
    }

    public function test_get_object_owned_by_account_from_non_owner_user()
    {
        $session = $this->getNew("Session");
        $session2 = $this->getNew("Session");
        $user = $session->getUser();
        $this->assertAPIError(\Bu\API::API_ERROR_FORBIDDEN(), "user/view", [ "user_id" => $user->getValue("user_id") ], $session2->getValue("hash"));
    }

    public function test_get_object_owned_by_custom_object()
    {
        $sessionchild = $this->getNew("SessionChild");
        $sessionchild_session = $sessionchild->getObject("session_id");
        $result = $this->assertAPISuccess("sessionchild/view", [ "sessionchild_id" => $sessionchild->getValue("sessionchild_id") ], $sessionchild_session->getValue("hash"));
        $this->assertEquals($result["sessionchild_id"], $sessionchild->getValue("sessionchild_id"));
    }

    public function test_get_object_owned_by_custom_object_from_non_owned_user()
    {
        $sessionchild = $this->getNew("SessionChild");
        $sessionchild_session = $sessionchild->getObject("session_id");
        $session2 = $this->getNew("Session");
        $this->assertAPIError(\Bu\API::API_ERROR_FORBIDDEN(), "sessionchild/view", [ "sessionchild_id" => $sessionchild->getValue("sessionchild_id") ], $session2->getValue("hash"));
    }
}
