<?php

    namespace Bu\Test;

    use PHPUnit\Framework\TestCase;

    class BuTest extends TestCase
    {
        use \Bu\Test\Factory;

        public function getRandomInt($lower = 0, $greater = 100)
        {
            return random_int($lower, $greater);
        }

        public function getRandomString($length = 10)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        public function getRandomUrl()
        {
            return "https://www." . $this->getRandomString() . ".com";
        }

        public function getRandomEmail()
        {
            return $this->getRandomString() . "@" . $this->getRandomString() . ".com";
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
						return $message;
				}

				public function assertAPIOK($method, $parameters, $session = null, $expectedMessage = null) {
					$api = \Bu\Test\Sample\API::get($method, $parameters, $session);
					$api->execute();
					$message = $api->getMessage();
					$this->assertEquals("success", $message["status"], json_encode($message));
					return isset($message["message"]) ? $message["message"] : [];
				}
    }
