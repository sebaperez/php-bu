<?php

namespace Bu\Test\Sample;

use Bu\Base;

class SampleClass extends Base
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
				]
			],
			"pk" => ["sampleclass_id"],
			"attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
		];
	}
}
