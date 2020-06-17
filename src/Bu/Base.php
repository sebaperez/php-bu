<?php

	namespace Bu;

	use Bu\Bu;
	use Bu\BuException;
	
	class Base extends Bu {

		// Field types

		public static function TYPE_INT() { return 1; }
		public static function TYPE_STRING() { return 2; }

		// Field attributes

		public static function ATTR_AUTO_INCREMENT() { return 1; }

		// Class attributes

		public static function ATTR_WITH_START_DATE() { return 1; }
		public static function ATTR_WITH_END_DATE() { return 2; }


		public static function getDef() {
			return get_called_class()::DEF();
		}

		public static function getTable() {
			return self::getDef()["table"];
		}

		public static function getPK() {
			return self::getDef()["pk"];
		}

		public static function hasSinglePK() {
			return count(self::getPK()) === 1;
		}

		public static function hasComposedPK() {
			return ! self::hasSinglePK();
		} 

		public static function get($ids = null) {
			if (! $ids) {
				throw new BuException_InvalidArgument("id not defined for Bu::get");
			} else if (self::hasComposedPK() && gettype($ids) !== "array") {
				throw new BuException_InvalidArgument("class has composed primary key - associative ids array expected");
			} else if (self::hasSinglePK() && gettype($ids) == "array") {
				throw new BuException_InvalidArgument("class has a single primary key - ids not expected as array");
			}
		}

	}
?>
