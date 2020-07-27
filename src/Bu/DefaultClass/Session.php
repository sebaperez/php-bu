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
                        "class" => "Bu\DefaultClass\User"
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
}

?>