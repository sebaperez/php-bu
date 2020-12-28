<?php

    namespace Bu;

    class Telegram extends Bu
    {

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

			public function run($test = false) {
				$commands = $this->getCommands();
				$command = $this->getCommand();
				if (isset($commands[$command])) {
					$this->response = $commands[$command]["function"]();
					if ($this->response && ! $test) {
						$this->sendResponse();
					}
					return true;
				}
			}

			public function getResponse() {
				return $this->response;
			}

		}

?>
