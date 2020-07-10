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
		$this->assertEquals($fieldsName, ["sampleclass_id", "name", "start_date", "end_date"]);
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
}
