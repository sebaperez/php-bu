<?php

namespace Bu\Test\Sample;

use Bu\Base;

class Telegram extends \Bu\Telegram
{

		public static function GET_DEFAULT_FK_CLASS_TELEGRAM_SESSION() {
			return "Bu\Test\Sample\TelegramSession";
		}

		public static function GET_DEFAULT_API_CLASS() {
			return "Bu\Test\Sample\API";
		}

		public function getToken() {
			return "";
		}

    public function getCommands() {
			return [
				"test" => [
					"apiMethod" => "test",
					"paramsFunction" => function() {
						return [];
					},
					"success" => function() {
						return "response";
					}
				],
				"test_nologged" => [
					"attrs" => [ self::TELEGRAM_ATTR_NO_LOGIN_REQUIRED() ],
					"apiMethod" => "telegram/test",
					"paramsFunction" => function() {
						return [];
					},
					"success" => function() {
						return "response";
					}
				],
				"fallback" => [
					"attrs" => [],
					"error" => function() {
						return "error";
					}
				]
			];
		}
}
