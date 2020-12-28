<?php

namespace Bu\BuUnit;

use Bu\Test\Sample\API;
use Bu\Test\Sample\TelegramSession;
use Bu\Test\BuTest;
use Bu\Base;

class TelegramTest extends \Bu\Test\BuTest {

	public static function CONFIG_CLASS()
	{
			return "\Bu\BuUnit\Config";
	}

	public function getTelegramJSON($text = "", $user_id = 111) {
		return '{"update_id":123,
"message":{"message_id":1,"from":{"id":' . $user_id . ',"is_bot":false,"first_name":"Name","last_name":"Lastname","username":"username","language_code":"en"},"chat":{"id":222,"first_name":"Name","last_name":"Lastname","username":"username","type":"private"},"date":1609185559,"text":"' . $text . '"}}';
	}

	public function test_get_response_from_command() {
		$COMMAND = "test_nologged";
		$telegram = new \Bu\Test\Sample\Telegram($this->getTelegramJSON($COMMAND));
		$this->assertNotNull($telegram);
		$this->assertTrue($telegram->run());
		$this->assertEquals("response", $telegram->getResponse());

		$this->assertEquals($COMMAND, $telegram->getText());
		$this->assertEquals(111, $telegram->getUserId());
		$this->assertEquals(222, $telegram->getChatId());
		$this->assertEquals(1609185559, $telegram->getDate());
		$this->assertEquals("username", $telegram->getUsername());
	}

	public function test_get_response_only_from_logged_user() {
		$USER_ID = 123;
		$session = $this->getNew("Session");
		$telegramSession = \Bu\Test\Sample\TelegramSession::add([
			"session_id" => $session->getValue("session_id"),
			"telegram_user_id" => $USER_ID
		]);
		$this->setToDelete($telegramSession);

		$COMMAND = "test";
		$telegram = new \Bu\Test\Sample\Telegram($this->getTelegramJSON($COMMAND, $USER_ID));
		$this->assertTrue($telegram->run());
		$this->assertNotNull($telegram->getResponse());

		$USER_ID = 124;
		$telegram = new \Bu\Test\Sample\Telegram($this->getTelegramJSON($COMMAND, $USER_ID));
		$this->assertTrue($telegram->run());
		$this->assertNull($telegram->getResponse());
	}

}

 ?>
