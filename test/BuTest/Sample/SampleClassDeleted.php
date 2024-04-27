<?php

namespace Bu\Test\Sample;

use Bu\Base;

class SampleClassDeleted extends Base
{
    public static function DEF()
    {
        return [
            "table" => "sampleclass",
            "fields" => [
                "sampleclass_id" => [
                    "type" => self::TYPE_INT(),
                    "attr" => [ self::ATTR_AUTO_INCREMENT() ]
                ],
                "name" => [
                    "type" => self::TYPE_STRING()
                ],
                "optional" => [
                    "type" => self::TYPE_STRING(),
                    "attr" => [ self::ATTR_OPTIONAL() ],
                    "validate" => [
                        "type" => self::VALIDATE_TYPE_EMAIL(),
                        "min_length" => 10,
                        "max_length" => 20
                    ]
                ],
								"value_validation" => [
										"type" => self::TYPE_INT(),
										"attr" => [ self::ATTR_OPTIONAL() ],
										"validate" => [
												"type" => self::VALIDATE_TYPE_NUMBER(),
												"min_value" => 10,
												"max_value" => 99
										]
								],
                "date" => [
                    "type" => self::TYPE_STRING(),
                    "attr" => [ self::ATTR_OPTIONAL() ],
                    "validate" => [
                        "type" => self::VALIDATE_TYPE_DATE(),
                        "min_date" => "2020-01-01",
                        "max_date" => "2020-01-31"
                    ]
                ],
                "time" => [
                    "type" => self::TYPE_STRING(),
                    "attr" => [ self::ATTR_OPTIONAL() ],
                    "validate" => [
                        "type" => self::VALIDATE_TYPE_TIME(),
                        "min_date" => "14:00:00",
                        "max_date" => "now"
                    ]
                ],
                "max_time" => [
                    "type" => self::TYPE_STRING(),
                    "attr" => [ self::ATTR_OPTIONAL() ],
                    "validate" => [
                        "type" => self::VALIDATE_TYPE_TIME(),
                        "max_date" => "now"
                    ]
                ],
            ],
            "pk" => ["sampleclass_id"],
            "attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE(), self::ATTR_LOADABLE_IF_DELETED() ]
        ];
    }
}
