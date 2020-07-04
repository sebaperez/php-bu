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
					"attr" => self::ATTR_AUTO_INCREMENT()
				],
				"name" => [
					"type" => self::TYPE_STRING()
				]
			],
			"pk" => ["sampleclass_id"],
			"attr" => [self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE()]
		];
	}
}
