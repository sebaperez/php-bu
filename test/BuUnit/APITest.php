<?php

namespace Bu\BuUnit;

use Bu\Test\Sample\SampleClass;
use Bu\Test\Sample\API;
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
        $api = \Bu\Test\Sample\API::get($method, $parameters, $session);
        $api->execute();
        $message = $api->getMessage();
        $this->assertEquals("error", $message["status"]);
        if ($expectedMessage) {
            if (isset($expectedMessage["errorCode"])) {
                $this->assertEquals($expectedMessage["errorCode"], $message["message"]["errorCode"], json_encode($message));
            }
						if (isset($expectedMessage["description"])) {
							$this->assertEquals($expectedMessage["description"], $message["message"]["description"], json_encode($message));
						}
        }
        return $api;
    }

		public function assertAPIOK($method, $parameters, $session = null, $expectedMessage = null) {
			$api = \Bu\Test\Sample\API::get($method, $parameters, $session);
			$api->execute();
			$message = $api->getMessage();
			$this->assertEquals("success", $message["status"], json_encode($message));
			return isset($message["message"]) ? $message["message"] : [];
		}

    public function test_invalid_method_fails()
    {
        $this->assertAPIError($this->getRandomString(), [], null, [
          "errorCode" => \Bu\API::API_ERROR_INVALID_METHOD()
        ]);
    }

		public function test_without_session_fails_if_requires_login() {
			$this->assertAPIError("class/command", [], null, [
				"errorCode" => \Bu\API::API_ERROR_FORBIDDEN()
			]);
		}

		public function getUserCredentials() {
			$password = $this->getRandomString();
			$user = $this->getNew("User", [ "password" => $password ]);
			$this->assertNotNull(\Bu\Test\Sample\User::validateCredentials($user->getValue("email"), $password));
			$session = \Bu\Test\Sample\User::getNewSession($user->getValue("email"), $password);
			$this->assertNotNull($session);
			return [
				"user" => $user,
				"password" => $password,
				"session" => $session,
				"sessionHash" => $session->getValue("hash")
			];
		}

		public function test_with_valid_session_pass() {
			$session = $this->getUserCredentials()["session"];
			$this->assertAPIOK("class/command", [], $session->getValue("hash"));
		}

		public function test_with_closed_session_fails() {
			$session = $this->getUserCredentials()["session"];
			$this->assertTrue($session->delete());

			$this->assertAPIError("class/command", [], $session->getValue("hash"), [
				"errorCode" => \Bu\API::API_ERROR_FORBIDDEN()
			]);
		}

		public function test_with_mandatory_params_missing_fails() {
			$this->assertAPIError("user/login", [ "password" => $this->getRandomString() ], null, [
				"errorCode" => \Bu\API::API_ERROR_MANDATORY_PARAMS_MISSING(),
				"description" => [ "email" ]
			]);
		}

		public function test_with_optional_params_dont_fail() {
			$credentials = $this->getUserCredentials();
			$response = $this->assertAPIOK("user/login", [
				"email" => $credentials["user"]->getValue("email"),
				"password" => $credentials["password"]
			]);
		}

		public function test_login() {
			$credentials = $this->getUserCredentials();
			$response = $this->assertAPIOK("user/login", [
				"email" => $credentials["user"]->getValue("email"),
				"password" => $credentials["password"]
			]);
			$this->assertNotNull($response["session"]);
			$session = \Bu\Test\Sample\Session::getByHash($response["session"]);
			$this->assertNotNull($credentials["user"]->getValue("user_id"));
			$this->assertEquals($credentials["user"]->getValue("user_id"), $session->getUser()->getValue("user_id"));
		}

		public function test_with_invalid_credentials_fail() {
			$credentials = $this->getUserCredentials();
			$this->assertAPIError("user/login", [
				"email" => $credentials["user"]->getValue("email"),
				"password" => $this->getRandomString()
			], null, [
				"errorCode" => \Bu\API::API_ERROR_LOGIN_WRONG_CREDENTIALS()
			]);
		}

		public function test_logout() {
			$credentials = $this->getUserCredentials();
			$sessionHash = $credentials["sessionHash"];
			$this->assertNotNull($sessionHash);
			$session = \Bu\Test\Sample\Session::getByHash($sessionHash);
			$this->assertNotNull($session);
			$response = $this->assertAPIOK("user/logout", [], $sessionHash);
			$session = \Bu\Test\Sample\Session::getByHash($sessionHash);
			$this->assertNull($session);
		}

		public function test_get_info_from_current_user() {
			$credentials = $this->getUserCredentials();
			$response = $this->assertAPIOK("user/current", [], $credentials["sessionHash"]);
			$user = \Bu\Test\Sample\User::get($response["user_id"]);
			$this->assertEquals($credentials["user"]->getValue("user_id"), $user->getValue("user_id"));
			$this->assertArrayNotHasKey("password", $response);
		}

		public function test_list_users() {
			$credentials = $this->getUserCredentials();
			$user1 = $credentials["user"];
			$user2 = $this->getNew("User", [ "account_id" => $user1->getValue("account_id") ]);
			$this->assertEquals($user1->getValue("account_id"), $user2->getValue("account_id"));
			$this->assertTrue($user1->grant("MANAGE_USERS"));
			$response = $this->assertAPIOK("user/list", [], $credentials["sessionHash"]);
			$this->assertCount(2, $response);
			$this->assertEquals($user1->getValue("user_id"), $response[0]["user_id"]);
			$this->assertEquals($user2->getValue("user_id"), $response[1]["user_id"]);
			$this->assertArrayNotHasKey("password", $response[0]);
			$this->assertArrayNotHasKey("password", $response[1]);
		}

		public function test_list_user_without_permission_fails() {
			$credentials = $this->getUserCredentials();
			$this->assertFalse($credentials["user"]->can("MANAGE_USERS"));
			$response = $this->assertAPIError("user/list", [], $credentials["sessionHash"], [
				"errorCode" => \Bu\API::API_ERROR_FORBIDDEN()
			]);
		}

}
