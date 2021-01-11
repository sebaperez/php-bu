<?php

    namespace Bu;

    class Telegram extends Bu
    {

			public static function TELEGRAM_ATTR_NO_LOGIN_REQUIRED() { return "TELEGRAM_ATTR_NO_LOGIN_REQUIRED"; }

			public function __construct($rawJson) {
				$this->rawJson = $rawJson;
				$this->json = json_decode($rawJson, true);
				$this->response = null;
			}

			public function getJson() {
				return $this->json;
			}

			public function getText() {
				return $this->getJson()["message"]["text"];
			}

			public function getParams() {
				$params = explode(" ", $this->getText());
				array_shift($params);
				return $params;
			}

			public function getCommand() {
				return explode(" ", $this->getText())[0];
			}

			public function getUserId() {
				return $this->getJson()["message"]["from"]["id"];
			}

			public function getChatId() {
				return $this->getJson()["message"]["chat"]["id"];
			}

			public function getDate() {
				return $this->getJson()["message"]["date"];
			}

			public function getUsername() {
				return $this->getJson()["message"]["from"]["username"];
			}

			public function getSession() {
				$telegramSession = get_called_class()::GET_DEFAULT_FK_CLASS_TELEGRAM_SESSION()::findByUserId($this->getUserId());
				if ($telegramSession) {
					return $telegramSession->getSession();
				}
			}

			public function sendResponse() {
				$token = $this->getToken();

				$params = http_build_query([
					"chat_id" => $this->getChatId(),
					"text" => $this->getResponse()
				]);

				$ch = curl_init("https://api.telegram.org/bot$token/sendMessage");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				$return = curl_exec($ch);
				$json = json_decode($return, true);
				if (isset($json["ok"]) && $json["ok"] === true) {
					return true;
				}
			}

			public function getCommandDefinition() {
				$commands = $this->getCommands();
				$command = $this->getCommand();
				return isset($commands[$command]) ? $commands[$command] : false;
			}

			public function getFallbackDefinition() {
				$commands = $this->getCommands();
				return $commands["fallback"];
			}

			public function isCommandDefined() {
				return (bool)$this->getCommandDefinition();
			}

			public function getCommandMethod($method) {
				if (isset($this->getCommandDefinition()[$method])) {
					return $this->getCommandDefinition()[$method];
				} else {
					return $this->getFallbackDefinition()[$method];
				}
			}

			public function getCommandAPIMethod() {
				return $this->getCommandMethod("apiMethod");
			}

			public function getCommandParamFunction() {
				return $this->getCommandMethod("paramsFunction");
			}

			public function getCommandSuccessFunction() {
				return $this->getCommandMethod("success");
			}

			public function getCommandErrorFunction() {
				return $this->getCommandMethod("error");
			}

			public function getCommandAttrs() {
				return $this->getCommandMethod("attrs");
			}

			public function hasAttr($attr) {
				return in_array($attr, $this->getCommandAttrs());
			}

			public function isLoginRequired() {
				return ! $this->hasAttr(self::TELEGRAM_ATTR_NO_LOGIN_REQUIRED());
			}

			public function run($test = false) {
				if ($this->isCommandDefined()) {
					$params = $this->getCommandParamFunction()($this->getParams());
					$session = $this->getSession();
					$sessionHash = isset($session) ? $session->getValue("hash") : null;
					if ($sessionHash || (! $sessionHash && ! $this->isLoginRequired())) {
						$api = get_called_class()::GET_DEFAULT_API_CLASS()::get($this->getCommandAPIMethod(), $params, $sessionHash);
						$api->execute();
						$message = $api->getMessage();
						if ("success" == $message["status"]) {
							$this->response = $this->getCommandSuccessFunction()($message["message"]);
						} else {
							$this->response = $this->getCommandErrorFunction()($message["message"]);
						}
						if ($this->response && ! $test) {
							$this->sendResponse();
						}
					}
					return true;
				}
			}

			public function getResponse() {
				return $this->response;
			}

		}

?>
