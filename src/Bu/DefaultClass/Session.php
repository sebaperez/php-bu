<?php

namespace Bu\DefaultClass;

use Bu\Base;

class Session extends \Bu\Base
{

	public static function DEF()
	{
		return [
			"table" => "session",
			"fields" => [
				"session_id" => [
					"type" => self::TYPE_INT(),
					"attr" => [ self::ATTR_AUTO_INCREMENT() ]
				],
				"user_id" => [
                    "type" => self::TYPE_INT(),
                    "fk" => [
                        "class" => get_called_class()::GET_DEFAULT_FK_CLASS_USER_ID()
                    ]
                ],
                "hash" => [
                    "type" => self::TYPE_STRING()
                ]
			],
			"pk" => [ "session_id" ],
			"attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
		];
	}

	public static function GET_DEFAULT_FK_CLASS_USER_ID() { return "Bu\DefaultClass\User"; }

	public static function DEFAULT_SESSION_HASH_LENGTH() { return 64; }

	public static function add($values = null) {
		$values["hash"] = self::getRandomString(self::DEFAULT_SESSION_HASH_LENGTH());
		return parent::add($values);
	}

	public function getUser() {
		return $this->getObject("user_id");
	}
}

?>