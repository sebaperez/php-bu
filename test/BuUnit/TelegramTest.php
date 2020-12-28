<?php

namespace Bu\BuUnit;

use Bu\Test\Sample\SampleClass;
use Bu\Test\Sample\API;
use Bu\Test\Sample\SampleClassMultiplePK;
use Bu\Test\BuTest;
use Bu\Base;

class TelegramTest extends \Bu\Test\BuTest {

	public function getTelegramJSON($text = "") {
		return '{"update_id":123,
"message":{"message_id":1,"from":{"id":111,"is_bot":false,"first_name":"Name","last_name":"Lastname","username":"username","language_code":"en"},"chat":{"id":222,"first_name":"Name","last_name":"Lastname","username":"username","type":"private"},"date":1609185559,"text":"' . $text . '"}}';
	}

	public function test_get_response_from_command() {
		$COMMAND = "test";
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

}

 ?>
