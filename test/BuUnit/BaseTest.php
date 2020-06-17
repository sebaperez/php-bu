<?php

	use Bu\Test\Sample\SampleClass;
	use Bu\Test\BuTest;
	use Bu\BuException;
	use Bu\Base;

	class BaseTest extends BuTest {

		public function test_get_table_name() {
			$this->assertEquals("sampleclass", SampleClass::getTable());
		}

		public function test_class_has_single_pk() {
			$this->assertTrue(SampleClass::hasSinglePK());
			$this->assertFalse(SampleClass::hasComposedPK());
		}

		public function test_instance_without_id_throw_exception() {
			$this->expectException(\Bu\BuException_InvalidArgument::class);
			$sampleclass = SampleClass::get();
		}

		public function test_instance_single_pk_class_with_array_throw_exception() {
			$this->expectException(\Bu\BuException_InvalidArgument::class);
			$sampleclass = SampleClass::get([ "id" => 1 ]);
		}

	}

?>
