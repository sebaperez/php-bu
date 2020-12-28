<?php

namespace Bu\Test\Sample;

use Bu\Base;

class Telegram extends \Bu\Telegram
{

		public function getToken() {
			return "";
		}

    public function getCommands() {
			return [
				"test" => [
					"function" => function() {
						return "response";
					}
				]
			];
		}
}
