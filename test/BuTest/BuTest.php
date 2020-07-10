<?php

	namespace Bu\Test;

	use PHPUnit\Framework\TestCase;

	class BuTest extends TestCase {

		use \Bu\Test\Factory;

		public function getRandomInt($lower = 0, $greater = 100) {
			return random_int($lower, $greater);
		}   

		public function getRandomString($length = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}

		public function getRandomUrl() {
			return "https://www." . $this->getRandomString() . ".com";
		}

	}
?>
