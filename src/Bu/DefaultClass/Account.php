<?php

namespace Bu\DefaultClass;

use Bu\Base;

class Account extends \Bu\Base
{

	public static function DEF()
	{
		return [
			"table" => "account",
			"fields" => [
				"account_id" => [
					"type" => self::TYPE_INT(),
					"attr" => [ self::ATTR_AUTO_INCREMENT() ]
				],
				"name" => [
					"type" => self::TYPE_STRING()
				]
			],
			"pk" => [ "account_id" ],
			"attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
		];
	}

    public static function GET_DEFAULT_FK_CLASS_USER_ID() { return "Bu\Test\Sample\User"; }

	public function validateUser($values) {
		return self::GET_DEFAULT_FK_CLASS_USER_ID()::validate($values);
	}

	public function addUser($values) {
		$userClass = self::GET_DEFAULT_FK_CLASS_USER_ID();
		return $userClass::add(array_merge(
			[ "account_id" =>  $this->getValue("account_id") ],
			$values
		));
	}
}

?>