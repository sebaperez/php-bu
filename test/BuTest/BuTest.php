<?php

    namespace Bu\Test;

    use PHPUnit\Framework\TestCase;

    abstract class BuTest extends TestCase
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
						$api = get_called_class()::API_CLASS()::get($method, $parameters, $session);
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
					$api = get_called_class()::API_CLASS()::get($method, $parameters, $session);
					$api->execute();
					$message = $api->getMessage();
					$this->assertEquals("success", $message["status"], json_encode($message));
					return isset($message["message"]) ? $message["message"] : [];
				}

				public function getTelegramJSON($text = "", $user_id = 111) {
					return '{"update_id":123,"message":{"message_id":1,"from":{"id":' . $user_id . ',"is_bot":false,"first_name":"Name","last_name":"Lastname","username":"username","language_code":"en"},"chat":{"id":222,"first_name":"Name","last_name":"Lastname","username":"username","type":"private"},"date":1609185559,"text":"' . $text . '"}}';
				}
		}
