<?php

use Bu\Test\Sample\SampleClass;
use Bu\Test\Sample\SampleClassMultiplePK;
use Bu\Test\BuTest;
use Bu\BuException;
use Bu\Base;

namespace Bu\BuUnit;

class BaseTest extends \Bu\Test\BuTest
{
	
	public static function CONFIG_CLASS() { return "\Bu\BuUnit\Config"; }

	public function test_arraysAreEqualsUnSorted()
	{
		$this->assertTrue(\Bu\Base::arraysAreEqualsUnSorted(["test1", "test2"], ["test1", "test2"]));
		$this->assertTrue(\Bu\Base::arraysAreEqualsUnSorted(["test2", "test1"], ["test1", "test2"]));

		$this->assertFalse(\Bu\Base::arraysAreEqualsUnSorted(["test1", "test2"], ["test1", "test2", "test3"]));
	}

	public function test_get_table_name()
	{
		$this->assertEquals("sampleclass", \Bu\Test\Sample\SampleClass::getTable());
	}

	public function test_get_pks()
	{
		$pks = \Bu\Test\Sample\SampleClass::getPK();
		$this->assertIsArray($pks);
		$this->assertEquals($pks, ["sampleclass_id"]);
	}

	public function test_class_has_single_pk()
	{
		$this->assertTrue(\Bu\Test\Sample\SampleClass::hasSinglePK());
		$this->assertFalse(\Bu\Test\Sample\SampleClass::hasComposedPK());
	}

	public function test_get_fields_names()
	{
		$fieldsName = \Bu\Test\Sample\SampleClass::getFieldNames();
		$this->assertIsArray($fieldsName);
		$this->assertEquals($fieldsName, ["sampleclass_id", "name", "optional", "date", "time", "start_date", "end_date"]);
	}

	public function test_is_valid_field()
	{
		$this->assertTrue(\Bu\Test\Sample\SampleClass::isField("name"));
		$this->assertFalse(\Bu\Test\Sample\SampleClass::isField("name1"));
	}

	public function test_get_field_type()
	{
		$this->assertEquals(\Bu\Base::TYPE_INT(), \Bu\Test\Sample\SampleClass::getFieldType("sampleclass_id"));
		$this->assertEquals(\Bu\Base::TYPE_STRING(), \Bu\Test\Sample\SampleClass::getFieldType("name"));
	}

	public function test_get_symbol_by_type()
	{
		$this->assertEquals("i", \Bu\Base::getSymbolByType(\Bu\Base::TYPE_INT()));
		$this->assertEquals("s", \Bu\Base::getSymbolByType(\Bu\Base::TYPE_STRING()));
	}

	public function test_get_field_symbol()
	{
		$this->assertEquals("i", \Bu\Test\Sample\SampleClass::getFieldSymbol("sampleclass_id"));
		$this->assertEquals("s", \Bu\Test\Sample\SampleClass::getFieldSymbol("name"));
	}

	public function test_instance_without_id_throws_exception()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleclass = \Bu\Test\Sample\SampleClass::get();
	}

	public function test_instance_single_pk_class_with_array_throws_exception()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleclass = \Bu\Test\Sample\SampleClass::get(["id" => 1]);
	}

	public function test_instance_multiple_pk_class_without_array_throws_exception()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleclass = \Bu\Test\Sample\SampleClassMultiplePK::get(1);
	}

	public function test_instance_fails_if_pk_is_missing()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleclass = \Bu\Test\Sample\SampleClassMultiplePK::get(["id1" => 1]);
	}

	public function test_instance_fails_if_pk_is_invalid()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleclass = \Bu\Test\Sample\SampleClassMultiplePK::get(["id1" => 1, "id2" => 2, "id3" => 3]);
	}

	public function test_get_object_values()
	{
		$_sampleobject = $this->getNew("SampleClass");
		$sampleobject = \Bu\Test\Sample\SampleClass::get($_sampleobject->getValue("sampleclass_id"));
		$this->assertNotNull($sampleobject->getValues());
	}

	public function test_add_missing_value()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleobject = \Bu\Test\Sample\SampleClass::add([]);
	}

	public function test_get_mandatory_fields()
	{
		$fields = \Bu\Test\Sample\SampleClass::getMandatoryFields();
		$this->assertEquals($fields, ["name"]);
	}

	public function test_add_invalid_value()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleobject = \Bu\Test\Sample\SampleClass::add(["name" => "test", "test" => 1]);
	}

	public function test_add_single_pk_cannot_be_set()
	{
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleobject = \Bu\Test\Sample\SampleClass::add(["name" => "test", "sampleclass_id" => 1]);
	}

	public function test_single_add()
	{
		$NAME = $this->getRandomString();
		$sampleobject = $this->getNew("SampleClass", [ "name" => $NAME ]);
		$this->assertNotNull($sampleobject);
		$this->assertEquals($NAME, $sampleobject->getValue("name"));
	}

	public function test_add_composed_pk()
	{
		$NAME = $this->getRandomString();
		$sampleobject1 = $this->getNew("SampleClass");
		$sampleobject2 = $this->getNew("SampleClass");
		$sampleclassmultiplepk = $this->getNew("SampleClassMultiplePK", [
			"id1" => $sampleobject1->getValue("sampleclass_id"),
			"id2" => $sampleobject2->getValue("sampleclass_id"),
			"name" => $NAME
		]);
		$this->assertNotNull($sampleclassmultiplepk);
		$this->assertEquals($NAME, $sampleclassmultiplepk->getValue("name"));
		$this->assertEquals($sampleobject1->getValue("sampleclass_id"), $sampleclassmultiplepk->getValue("id1"));
		$this->assertEquals($sampleobject2->getValue("sampleclass_id"), $sampleclassmultiplepk->getValue("id2"));
	}

	public function test_set_value()
	{
		$sampleobject = $this->getNew("SampleClass");
		$NEWNAME = $this->getRandomString();
		$this->assertTrue($sampleobject->_setValue("name", $NEWNAME));
		$this->assertEquals($NEWNAME, $sampleobject->getValue("name"));
	}

	public function test_update_field_on_simple_pk()
	{
		$sampleobject = $this->getNew("SampleClass");
		$NEWNAME = $this->getRandomString();
		$this->assertTrue($sampleobject->update("name", $NEWNAME));
		$this->assertEquals($NEWNAME, $sampleobject->getValue("name"));
		$_sampleobject = \Bu\Test\Sample\SampleClass::get($sampleobject->getValue("sampleclass_id"));
		$this->assertEquals($NEWNAME, $_sampleobject->getValue("name"));
	}

	public function test_update_field_on_composed_pk()
	{
		$sampleobject1 = $this->getNew("SampleClass");
		$sampleobject2 = $this->getNew("SampleClass");
		$NAME = $this->getRandomString();
		$sampleclassmultiplepk = $this->getNew("SampleClassMultiplePK", [
			"id1" => $sampleobject1->getValue("sampleclass_id"),
			"id2" => $sampleobject2->getValue("sampleclass_id"),
			"name" => $NAME
		]);
		$NEWNAME = $this->getRandomString();
		$this->assertTrue($sampleclassmultiplepk->update("name", $NEWNAME));
		$this->assertEquals($NEWNAME, $sampleclassmultiplepk->getValue("name"));
		$_sampleclassmultiplepk = \Bu\Test\Sample\SampleClassMultiplePK::get([
			"id1" => $sampleclassmultiplepk->getValue("id1"),
			"id2" => $sampleclassmultiplepk->getValue("id2"),
		]);
		$this->assertEquals($NEWNAME, $_sampleclassmultiplepk->getValue("name"));
	}

	public function test_update_invalid_field()
	{
		$sampleobject = $this->getNew("SampleClass");
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleobject->update("test", 1);
	}

	public function test_update_without_field()
	{
		$sampleobject = $this->getNew("SampleClass");
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleobject->update(null, 1);
	}

	public function test_update_without_value()
	{
		$sampleobject = $this->getNew("SampleClass");
		$this->expectException(\Bu\Exception\InvalidArgument::class);
		$sampleobject->update("name");
	}

	public function test_has_start_date()
	{
		$this->assertTrue(\Bu\Test\Sample\SampleClass::hasStartDate());
	}

	public function test_has_end_date()
	{
		$this->assertTrue(\Bu\Test\Sample\SampleClass::hasEndDate());
	}

	public function test_delete()
	{
		$sampleobject = $this->getNew("SampleClass");
		$this->assertFalse($sampleobject->isDeleted());
		$this->assertTrue($sampleobject->delete());
		$this->assertTrue($sampleobject->isDeleted());

		$this->expectException(\Bu\Exception\InvalidObject::class);
		$_sampleobject = \Bu\Test\Sample\SampleClass::get($sampleobject->getValue("sampleclass_id"));
	}

	public function test_find()
	{
		$NAME = "test";
		$sampleobject = $this->getNew("SampleClass", [ "name" => $NAME ]);
		$objects = \Bu\Test\Sample\SampleClass::find("name = ?", ["name" => $NAME]);
		$this->assertTrue(count($objects) >= 1);
		$flag = false;
		foreach ($objects as $object) {
			$this->assertEquals($NAME, $object->getValue("name"));
			if ($object->getValue("sampleclass_id") === $sampleobject->getValue("sampleclass_id")) {
				$flag = true;
			}
		}
		$this->assertTrue($flag);
	}

	public function test_find_exclude_end_date()
	{
		$NAME = "test";
		$sampleobject = $this->getNew("SampleClass", [ "name" => $NAME ]);
		$this->assertTrue($sampleobject->delete());
		$objects = \Bu\Test\Sample\SampleClass::find("name = ?", ["name" => $NAME]);
		$flag = false;
		foreach ($objects as $object) {
			$this->assertEquals($NAME, $object->getValue("name"));
			if ($object->getValue("sampleclass_id") === $sampleobject->getValue("sampleclass_id")) {
				$flag = true;
			}
		}
		$this->assertFalse($flag);
	}

	public function test_find_composed_pk()
	{
		$NAME = $this->getRandomString();
		$samplecomposed = $this->getNew("SampleClassMultiplePK", [ "name" => $NAME ]);

		$objects = \Bu\Test\Sample\SampleClassMultiplePK::find("name = ?", [ "name" => $NAME ]);
		$this->assertTrue(count($objects) >= 1);
		$flag = false;
		foreach ($objects as $object) {
			$this->assertEquals($NAME, $object->getValue("name"));
			if ($object->getValue("id1") === $samplecomposed->getValue("id1") && $object->getValue("id2") === $samplecomposed->getValue("id2")) {
				$flag = true;
			}
		}
		$this->assertTrue($flag);
	}

	public function test_find_all()
	{
		$sampleobject = $this->getNew("SampleClass");
		$objects = \Bu\Test\Sample\SampleClass::findAll();
		foreach ($objects as $object) {
			$this->assertEquals($sampleobject->getClassName(), $object->getClassName());
		}
	}

	public function test_get_fk_object() {
		$sampleobject1 = $this->getNew("SampleClass");
		$sampleobject2 = $this->getNew("SampleClass");
		$sampleclassmultiplepk = $this->getNew("SampleClassMultiplePK", [
			"id1" => $sampleobject1->getValue("sampleclass_id"),
			"id2" => $sampleobject2->getValue("sampleclass_id")
		]);
		$this->assertEquals($sampleclassmultiplepk->getObject("id1")->getValue("sampleclass_id"), $sampleobject1->getValue("sampleclass_id"));
		$this->assertEquals($sampleclassmultiplepk->getObject("id2")->getValue("sampleclass_id"), $sampleobject2->getValue("sampleclass_id"));
	}

	public function test_find_objects() {
		$sampleobject1 = $this->getNew("SampleClass");
		$sampleobject2 = $this->getNew("SampleClass");
		$sampleobject3 = $this->getNew("SampleClass");
		$sampleclassmultiplepk1 = $this->getNew("SampleClassMultiplePK", [
			"id1" => $sampleobject1->getValue("sampleclass_id"),
			"id2" => $sampleobject2->getValue("sampleclass_id")
		]);
		$sampleclassmultiplepk2 = $this->getNew("SampleClassMultiplePK", [
			"id1" => $sampleobject1->getValue("sampleclass_id"),
			"id2" => $sampleobject3->getValue("sampleclass_id")
		]);

		$objects = $sampleobject1->findObjects("\Bu\Test\Sample\SampleClassMultiplePK");
		$this->assertNotEmpty($objects);
		$this->assertCount(2, $objects);

		$this->assertEquals($objects[0]->getValue("id1"), $sampleobject1->getValue("sampleclass_id"));
		$this->assertEquals($objects[0]->getValue("id2"), $sampleobject2->getValue("sampleclass_id"));

		$this->assertEquals($objects[1]->getValue("id1"), $sampleobject1->getValue("sampleclass_id"));
		$this->assertEquals($objects[1]->getValue("id2"), $sampleobject3->getValue("sampleclass_id"));
		
	}

	public function test_validate_missing_fields() {
		$validation = \Bu\Test\Sample\SampleClass::validate([]);
		$this->assertNotEmpty($validation);
		$this->assertCount(1, $validation);
		$this->assertArrayHasKey("name", $validation);
		$this->assertArrayHasKey("error", $validation["name"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_MISSING_FIELD(), $validation["name"]["error"]);
	}

	public function test_validate_type() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"optional" => $this->getRandomString()
		]);
		$this->assertCount(1, $validation);
		$this->assertArrayHasKey("optional", $validation);
		$this->assertArrayHasKey("error", $validation["optional"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_TYPE(), $validation["optional"]["error"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_TYPE_EMAIL(), $validation["optional"]["details"]["type"]);
	}

	public function test_validate_min_length() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"optional" => $this->getRandomString(1) . "@t.com"
		]);
		$this->assertArrayHasKey("optional", $validation);
		$this->assertArrayHasKey("error", $validation["optional"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_LENGTH(), $validation["optional"]["error"]);
		$this->assertEquals(10, $validation["optional"]["details"]["min_length"]);
	}

	public function test_validate_max_length() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"optional" => $this->getRandomString(15) . "@t.com"
		]);
		$this->assertArrayHasKey("optional", $validation);
		$this->assertArrayHasKey("error", $validation["optional"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_LENGTH(), $validation["optional"]["error"]);
		$this->assertEquals(20, $validation["optional"]["details"]["max_length"]);
	}

	public function test_validate_min_date() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"date" => "2019-01-01"
		]);
		$this->assertArrayHasKey("date", $validation);
		$this->assertArrayHasKey("error", $validation["date"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["date"]["error"]);
		$this->assertEquals("2020-01-01", $validation["date"]["details"]["min_date"]);
	}

	public function test_validate_max_date() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"date" => "2021-01-01"
		]);
		$this->assertArrayHasKey("date", $validation);
		$this->assertArrayHasKey("error", $validation["date"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["date"]["error"]);
		$this->assertEquals("2020-01-31", $validation["date"]["details"]["max_date"]);
	}

	public function test_validate_min_time() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"time" => "07:00:00"
		]);
		$this->assertArrayHasKey("time", $validation);
		$this->assertArrayHasKey("error", $validation["time"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["time"]["error"]);
		$this->assertEquals("14:00:00", $validation["time"]["details"]["min_date"]);
	}

	public function test_validate_max_time() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"time" => "18:00:00"
		]);
		$this->assertArrayHasKey("time", $validation);
		$this->assertArrayHasKey("error", $validation["time"]);
		$this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["time"]["error"]);
		$this->assertEquals("15:00:00", $validation["time"]["details"]["max_date"]);
	}

	public function test_validate_ok() {
		$validation = \Bu\Test\Sample\SampleClass::validate([
			"name" => $this->getRandomString(),
			"optional" => $this->getRandomString(10) . "@t.com"
		]);
		$this->assertEmpty($validation);
	}

	public function assertValidType($type, $value) {
		return $this->assertTrue(\Bu\Base::validateType($type, $value), "Failed asserting that $value is a valid $type");
	}

	public function assertNotValidType($type, $value) {
		return $this->assertFalse(\Bu\Base::validateType($type, $value), "Failed asserting that $value is not a valid $type");
	}

	public function assertValid($type, $values) {
		foreach ($values["valid"] as $value) {
			$this->assertValidType($type, $value);
		}
		foreach ($values["invalid"] as $value) {
			$this->assertNotValidType($type, $value);
		}
	}

	public function test_valid_email() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_EMAIL(), [
			"valid" => [
				"test@test.com",
				"test@test.edu.ar",
				"test@rare.domain"
			],
			"invalid" => [
				"@test.com",
				"test.com",
				$this->getRandomString()
			] 
		]);
	}

	public function test_valid_url() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_URL(), [
			"valid" => [
				"http://test.com",
				"https://test.com",
				"http://test.com/test",
				"http://www.test.com/test",
				"http://www.test.com/test.html",
				"http://www.test.com/test.html?p=1",
				"http://www.test.com/test.html?p=1&t=2",
				"ftp://test.com"
			],
			"invalid" => [
				"test.com",
				"test.com/url",
				$this->getRandomString(),
				"1",
				1
			]
		]);
	}

	public function test_valid_domain() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_DOMAIN(), [
			"valid" => [
				"test.com",
				"rare.domain"
			],
			"invalid" => [
				"http://test.com",
				$this->getRandomString(),
				"1",
				1
			]
		]);
	}

	public function test_valid_string() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_STRING(), [
			"valid" => [
				$this->getRandomString(),
				"test.com",
				"1"
			],
			"invalid" => [
				1
			]
		]);		
	}

	public function test_valid_int() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_INT(), [
			"valid" => [
				1
			],
			"invalid" => [
				1.0,
				1.2,
				$this->getRandomString(),
				"1"
			]
		]);		
	}

	public function test_valid_number() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_NUMBER(), [
			"valid" => [
				1,
				1.0,
				1.2,
				"1",
				"1.2"
			],
			"invalid" => [
				$this->getRandomString()
			]
		]);		
	}

	public function test_valid_date() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_DATE(), [
			"valid" => [
				"2020-01-01"
			],
			"invalid" => [
				$this->getRandomString(),
				"2020-01-01 14:14:14",
				"2020-13-01",
				"2020-02-30"
			]
		]);		
	}

	public function test_valid_datetime() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_DATETIME(), [
			"valid" => [
				"2020-01-01 14:14:14",
			],
			"invalid" => [
				$this->getRandomString(),
				"2020-01-01",
				"2020-13-01",
				"2020-02-30",
				"14:14:14",
				"2020-01-01 24:14:14",
				"2020-01-01 14:14:60",
				"2020-01-01 14:60:14"
			]
		]);		
	}

	public function test_valid_time() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_TIME(), [
			"valid" => [
				"14:14:14"
			],
			"invalid" => [
				$this->getRandomString(),
				"2020-01-01",
				"2020-13-01",
				"2020-02-30",
				"2020-01-01 14:14:14",
				"24:14:14",
				"14:14:60",
				"14:60:14"
			]
		]);		
	}

	public function test_valid_time_hour_minute() {
		$this->assertValid(\Bu\Base::VALIDATE_TYPE_HOUR_MINUTE(), [
			"valid" => [
				"14:14"
			],
			"invalid" => [
				$this->getRandomString(),
				"2020-01-01",
				"2020-13-01",
				"2020-02-30",
				"2020-01-01 14:14:14",
				"14:14:14",
				"24:14",
				"14:60"
			]
		]);		
	}

}