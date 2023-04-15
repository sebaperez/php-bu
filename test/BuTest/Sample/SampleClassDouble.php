<?php

namespace Bu\Test\Sample;

use Bu\Base;

class SampleClassDouble extends Base
{
    public static function DEF()
    {
        return [
            "table" => "sampleclassdouble",
            "fields" => [
                "sampleclassdouble_id" => [
                    "type" => self::TYPE_INT(),
                    "attr" => [ self::ATTR_AUTO_INCREMENT() ]
                ],
                "value" => [
                    "type" => self::TYPE_DOUBLE()
                ]
            ],
            "pk" => ["sampleclassdouble_id"],
            "attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
        ];
    }
}
