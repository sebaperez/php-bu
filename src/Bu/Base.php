<?php

	namespace Bu;

	use Bu\Bu;
	use Bu\BuException;
	
	class Base extends Bu {

		private $values;

		// Field types

		public static function TYPE_INT() { return 1; }
		public static function TYPE_STRING() { return 2; }

		public static function SYMBOLS_BY_TYPE() {
			return [
				self::TYPE_INT() => "i",
				self::TYPE_STRING() => "s"
			];
		}

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

		public static function getField($field) {
			return self::getFields()[$field];
		}

		public static function getFieldType($field) {
			return self::getField($field)["type"];
		}

		public static function getFieldSymbol($field) {
			return self::getSymbolByType(self::getFieldType($field));
		}

		public static function getFields() {
			return self::getDef()["fields"];
		}

		public static function getSymbolByType($type) {
			return self::SYMBOLS_BY_TYPE()[$type];
		}

		public static function getFieldNames() {
			return array_keys(self::getFields());
		}

		public static function hasSinglePK() {
			return count(self::getPK()) === 1;
		}

		public static function hasComposedPK() {
			return ! self::hasSinglePK();
		}

		public function __construct($data) {
			$this->values = $data["values"];
		}

		public function getValues() {
			return $this->values;
		}

		public static function get($ids = null) {
			if (! $ids) {
				throw new \Bu\Exception\InvalidArgument("id not defined for Bu::get");
			} else if (self::hasComposedPK() && gettype($ids) !== "array") {
				throw new \Bu\Exception\InvalidArgument("class has composed primary key - associative ids array expected");
			} else if (self::hasSinglePK() && gettype($ids) == "array") {
				throw new \Bu\Exception\InvalidArgument("class has a single primary key - ids not expected as array");
			} else if (self::hasComposedPK() && ! self::arraysAreEqualsUnSorted($ids, self::getPK())) {
				throw new \Bu\Exception\InvalidArgument("missing or invalid PKs");
			}

			$class = get_called_class();
			if (self::hasSinglePK()) {
				$ids = [ self::getPK()[0] => $ids ];
			}
			$values = BuDB::getValuesSingleObject($class, $ids);

			if ($values) {
				return new $class([ "values" => $values ]);
			} else {
				throw new \Bu\Exception\InvalidObject();
			}
		}

	}
?>
