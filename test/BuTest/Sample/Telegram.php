<?php

namespace Bu\Test\Sample;

use Bu\Base;

class Telegram extends \Bu\Telegram
{

		public static function GET_DEFAULT_FK_CLASS_TELEGRAM_SESSION() {
			return "Bu\Test\Sample\TelegramSession";
		}

		public function getToken() {
			return "";
		}

    public function getCommands() {
			return [
				"test" => [
					"function" => function() {
						$session = $this->getSession();
						if ($session) {
							return "response";
						}
					}
				],
				"test_nologged" => [
					"function" => function() {
						return "response";
					}
				]
			];
		}
}
