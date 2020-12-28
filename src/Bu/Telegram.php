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

			public function run() {
				$commands = $this->getCommands();
				$command = $this->getCommand();
				if (isset($commands[$command])) {
					$this->response = $commands[$command]["function"]();
					return true;
				}
			}

			public function getResponse() {
				return $this->response;
			}

		}

?>
