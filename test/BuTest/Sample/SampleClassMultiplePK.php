<?php

    namespace Bu\Test\Sample;

    use Bu\Base;

    class SampleClassMultiplePK extends Base
    {
        public static function DEF()
        {
            return [
                "table" => "sampleclassmultiplepk",
                "fields" => [
                    "id1" => [
                        "type" => self::TYPE_INT(),
                        "fk" => [
                            "class" => "Bu\Test\Sample\SampleClass"
                        ]
                    ],
                    "id2" => [
                        "type" => self::TYPE_INT(),
                        "fk" => [
                            "class" => "Bu\Test\Sample\SampleClass"
                        ]
                    ],
                    "name" => [
                        "type" => self::TYPE_STRING()
                    ]
                ],
                "pk" => [ "id1", "id2" ],
                "attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
            ];
        }
    }
