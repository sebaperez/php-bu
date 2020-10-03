<?php

namespace Bu\Test\Sample;

use Bu\Base;

class SessionChild extends Base
{
    public static function DEF()
    {
        return [
            "table" => "sessionchild",
            "fields" => [
                "sessionchild_id" => [
                    "type" => self::TYPE_INT(),
                    "attr" => [ self::ATTR_AUTO_INCREMENT() ]
                ],
                "session_id" => [
                  "type" => self::TYPE_INT(),
                  "fk" => [
                      "class" => "Bu\Test\Sample\Session",
                      "attr" => [ self::ATTR_FK_IS_OWNER() ]
                  ]
                ]
            ],
            "pk" => [ "sessionchild_id" ],
            "attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
        ];
    }
}
