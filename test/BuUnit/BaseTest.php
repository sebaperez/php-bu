<?php

	use Bu\Test\Sample\SampleClass;
	use Bu\Test\Sample\SampleClassMultiplePK;
	use Bu\Test\BuTest;
	use Bu\BuException;
	use Bu\Base;

	class BaseTest extends BuTest {

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

	}

?>
