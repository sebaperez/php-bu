<?php

	namespace Bu\BuUnit;

	use Bu\Test\BuTest;

	class CreatorTest extends \Bu\Test\BuTest {

		public static function CONFIG_CLASS() {
			return "\Bu\BuUnit\Config";
		}

		public function test_get_array_from_definition() {
			$NAMESPACE = "spacetest";
			$DEFINITION = file_get_contents("test/BuTest/Sample/extra/sampleCreator.txt");
			$creator = \Bu\Dev\Creator::init($NAMESPACE, $DEFINITION);
			$array = $creator->getArrayFromDefinition();
			$this->assertNotNull($array);
			$this->assertNotNull($array["user"]);
			$this->assertNotNull($array["account"]);
			$this->assertEquals($array["user"]["pk"], ["user_id", "name"]);
			$this->assertEquals($array["account"]["pk"], ["account_id"]);
		}

	}

?>
