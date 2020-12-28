<?php

namespace Bu\Test\Sample;

use Bu\Base;

class Telegram extends \Bu\Telegram
{

		public function getToken() {
			return "1408890635:AAGnl6rydxoigoEA5K9QrKiUxZMDXrMWeLM";
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
