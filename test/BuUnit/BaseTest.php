<?php

	use Bu\Test\Sample\SampleClass;
	use Bu\Test\Sample\SampleClassMultiplePK;
	use Bu\Test\BuTest;
	use Bu\BuException;
	use Bu\Base;

	class BaseTest extends BuTest {

		public function test_arraysAreEqualsUnSorted() {
			$this->assertTrue(\Bu\Base::arraysAreEqualsUnSorted(["test1", "test2"], ["test1", "test2"]));
			$this->assertTrue(\Bu\Base::arraysAreEqualsUnSorted(["test2", "test1"], ["test1", "test2"]));

			$this->assertFalse(\Bu\Base::arraysAreEqualsUnSorted(["test1", "test2"], ["test1", "test2", "test3"]));
		}

		public function test_get_table_name() {
			$this->assertEquals("sampleclass", SampleClass::getTable());
		}

		public function test_get_pks() {
			$pks = SampleClass::getPK();
			$this->assertIsArray($pks);
			$this->assertEquals($pks, [ "sampleclass_id" ]);
		}

		public function test_class_has_single_pk() {
			$this->assertTrue(SampleClass::hasSinglePK());
			$this->assertFalse(SampleClass::hasComposedPK());
		}

		public function test_get_fields_names() {
			$fieldsName = SampleClass::getFieldNames();
			$this->assertIsArray($fieldsName);
			$this->assertEquals($fieldsName, [ "sampleclass_id", "name" ]);
		}

		public function test_is_valid_field() {
			$this->assertTrue(SampleClass::isField("name"));
			$this->assertFalse(SampleClass::isField("name1"));
		}

		public function test_get_field_type() {
			$this->assertEquals(\Bu\Base::TYPE_INT(), SampleClass::getFieldType("sampleclass_id"));
			$this->assertEquals(\Bu\Base::TYPE_STRING(), SampleClass::getFieldType("name"));
		}

		public function test_get_symbol_by_type() {
			$this->assertEquals("i", \Bu\Base::getSymbolByType(\Bu\Base::TYPE_INT()));
			$this->assertEquals("s", \Bu\Base::getSymbolByType(\Bu\Base::TYPE_STRING()));
		}

		public function test_get_field_symbol() {
			$this->assertEquals("i", SampleClass::getFieldSymbol("sampleclass_id"));
			$this->assertEquals("s", SampleClass::getFieldSymbol("name"));
		}

		public function test_instance_without_id_throws_exception() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleclass = SampleClass::get();
		}

		public function test_instance_single_pk_class_with_array_throws_exception() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleclass = SampleClass::get([ "id" => 1 ]);
		}

		public function test_instance_multiple_pk_class_without_array_throws_exception() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleclass = SampleClassMultiplePK::get(1);
		}

		public function test_instance_fails_if_pk_is_missing() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleclass = SampleClassMultiplePK::get([ "id1" => 1 ]);
		}

		public function test_instance_fails_if_pk_is_invalid() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleclass = SampleClassMultiplePK::get([ "id1" => 1, "id2" => 2, "id3" => 3 ]);
		}

		public function test_get_object_values() {
			$sampleobject = SampleClass::get(1);
			$this->assertNotNull($sampleobject->getValues());
		}

		public function test_add_missing_value() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleobject = SampleClass::add([]);
		}

		public function test_get_mandatory_fields() {
			$fields = SampleClass::getMandatoryFields();
			$this->assertEquals($fields, [ "name" ]);
		}

		public function test_add_invalid_value() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleobject = SampleClass::add([ "name" => "test", "test" => 1 ]);
		}

		public function test_add_single_pk_cannot_be_set() {
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleobject = SampleClass::add([ "name" => "test", "sampleclass_id" => 1 ]);
		}

		public function test_single_add() {
			$NAME = "test";
			$sampleobject = SampleClass::add([ "name" => $NAME ]);
			$this->assertNotNull($sampleobject);
			$this->assertEquals($NAME, $sampleobject->getValue("name"));
		}

		public function test_add_composed_pk() {
			$NAME = "testcomposed";
			$sampleobject1 = SampleClass::add([ "name" => "test" ]);
			$sampleobject2 = SampleClass::add([ "name" => "test" ]);
			$sampleclassmultiplepk = SampleClassMultiplePK::add([
				"id1" => $sampleobject1->getValue("sampleclass_id"),
				"id2" => $sampleobject2->getValue("sampleclass_id"),
				"name" => $NAME
			]);
			$this->assertNotNull($sampleclassmultiplepk);
			$this->assertEquals($NAME, $sampleclassmultiplepk->getValue("name"));
			$this->assertEquals($sampleobject1->getValue("sampleclass_id"), $sampleclassmultiplepk->getValue("id1"));
			$this->assertEquals($sampleobject2->getValue("sampleclass_id"), $sampleclassmultiplepk->getValue("id2"));
			
		}

		public function test_set_value() {
			$sampleobject1 = SampleClass::add([ "name" => "test" ]);
			$NEWNAME = "test2";
			$this->assertTrue($sampleobject1->_setValue("name", $NEWNAME));
			$this->assertEquals($NEWNAME, $sampleobject1->getValue("name"));
		}

		public function test_update_field() {
			$sampleobject1 = SampleClass::add([ "name" => "test" ]);
			$NEWNAME = "test2";
			$this->assertTrue($sampleobject1->update("name", $NEWNAME));
			$this->assertEquals($NEWNAME, $sampleobject1->getValue("name"));
			$_sampleobject1 = SampleClass::get($sampleobject1->getValue("sampleclass_id"));
			$this->assertEquals($NEWNAME, $_sampleobject1->getValue("name"));
		}

		public function test_update_invalid_field() {
			$sampleobject = SampleClass::add([ "name" => "test" ]);
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleobject->update("test", 1);
		}

		public function test_update_without_field() {
			$sampleobject = SampleClass::add([ "name" => "test" ]);
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleobject->update(null, 1);
		}

		public function test_update_without_value() {
			$sampleobject = SampleClass::add([ "name" => "test" ]);
			$this->expectException(\Bu\Exception\InvalidArgument::class);
			$sampleobject->update("name");
		}

	}

?>
