<?php

namespace Bu\DefaultClass;

use Bu\Base;

class User extends \Bu\Base
{

	public static function DEF()
	{
		return [
			"table" => "user",
			"fields" => [
				"user_id" => [
					"type" => self::TYPE_INT(),
					"attr" => [ self::ATTR_AUTO_INCREMENT() ]
                ],
                "account_id" => [
                    "type" => self::TYPE_INT(),
                    "fk" => [
                        "class" => self::GET_DEFAULT_FK_CLASS_ACCOUNT_ID()
                    ]
                ],
                "email" => [
					"type" => self::TYPE_STRING()
                ],
				"name" => [
					"type" => self::TYPE_STRING()
                ],
                "lastname" => [
                    "type" => self::TYPE_STRING(),
                    "attr" => [ self::ATTR_OPTIONAL() ]
                ],
                "password" => [
                    "type" => self::TYPE_STRING()
                ]
			],
			"pk" => [ "user_id" ],
			"attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
		];
    }
    
    public static function GET_DEFAULT_FK_CLASS_ACCOUNT_ID() { return "Bu\DefaultClass\Account"; }

}

?>