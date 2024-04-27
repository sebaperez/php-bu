<?php

    namespace Bu;

    use Bu\Bu;
    use Bu\BuException;

    class Base extends Bu
    {
        use Validate;

        private $values;

        // Field types

        public static function TYPE_INT()
        {
            return 1;
        }
        public static function TYPE_STRING()
        {
            return 2;
        }
        public static function TYPE_DATE()
        {
            return 3;
        }
	public static function TYPE_DOUBLE() {
	    return 4;
	}

        public static function STRING_FIELD_START_DATE()
        {
            return "start_date";
        }
        public static function STRING_FIELD_END_DATE()
        {
            return "end_date";
        }

        public static function SYMBOLS_BY_TYPE()
        {
            return [
                self::TYPE_INT() => "i",
                self::TYPE_STRING() => "s",
                self::TYPE_DATE() => "s",
		self::TYPE_DOUBLE() => "d"
            ];
        }

        // Field attributes

        public static function ATTR_AUTO_INCREMENT()
        {
            return 1;
        }
        public static function ATTR_OPTIONAL()
        {
            return 2;
        }
        public static function ATTR_SELF_DEFINED()
        {
            return 3;
        }
				public static function ATTR_NOT_VISIBLE() {
						return 4;
				}

        // Class attributes

        public static function ATTR_WITH_START_DATE()
        {
            return 1;
        }
        public static function ATTR_WITH_END_DATE()
        {
            return 2;
        }
	public static function ATTR_LOADABLE_IF_DELETED() {
	    return 3;
	}

        public static function getDef()
        {
            return get_called_class()::DEF();
        }

        public static function getTable()
        {
            return self::getDef()["table"];
        }

        public static function getPK()
        {
            return self::getDef()["pk"];
        }

        public static function getField($field)
        {
            $fields = self::getFields();
            if (isset($fields[$field])) {
                return $fields[$field];
            }
        }

        public static function getFieldType($field)
        {
            return self::getField($field)["type"];
        }

        public static function isFieldFK($field)
        {
            return isset(self::getField($field)["fk"]);
        }

        public static function getFieldFK($field)
        {
            return self::getField($field)["fk"];
        }

        public static function getFieldFKClass($field)
        {
            return self::getFieldFK($field)["class"];
        }

        public static function getCommonFieldSymbols()
        {
            return [
                self::STRING_FIELD_START_DATE() => self::getSymbolByType(self::TYPE_DATE()),
                self::STRING_FIELD_END_DATE() => self::getSymbolByType(self::TYPE_DATE())
            ];
        }

        public static function isCommonField($field)
        {
            return isset(self::getCommonFieldSymbols()[$field]);
        }

        public static function getCommonFieldSymbol($field)
        {
            return self::getCommonFieldSymbols()[$field];
        }

        public static function getFieldSymbol($field)
        {
            if (self::isCommonField($field)) {
                return self::getCommonFieldSymbol($field);
            }
            return self::getSymbolByType(self::getFieldType($field));
        }

        public static function getFields()
        {
            return self::getDef()["fields"];
        }

        public static function getSymbolByType($type)
        {
            return self::SYMBOLS_BY_TYPE()[$type];
        }

        public static function getFieldNames()
        {
            $fields = array_keys(self::getFields());
            if (self::hasStartDate()) {
                array_push($fields, self::STRING_FIELD_START_DATE());
            }
            if (self::hasEndDate()) {
                array_push($fields, self::STRING_FIELD_END_DATE());
            }
            return $fields;
        }

        public static function isField($field)
        {
            return in_array($field, self::getFieldNames());
        }

        public static function hasSinglePK()
        {
            return count(self::getPK()) === 1;
        }

        public static function hasComposedPK()
        {
            return ! self::hasSinglePK();
        }

        public static function getAttr()
        {
            return self::getDef()["attr"];
        }

        public static function hasStartDate()
        {
            return in_array(self::ATTR_WITH_START_DATE(), self::getAttr());
        }

        public static function hasEndDate()
        {
            return in_array(self::ATTR_WITH_END_DATE(), self::getAttr());
        }

	public static function isLoadableIfDeleted() {
	    return in_array(self::ATTR_LOADABLE_IF_DELETED(), self::getAttr());
	}

        public static function isFieldMandatory($fieldName)
        {
            $field = self::getField($fieldName);
            if (! isset($field["attr"])) {
                return true;
            } elseif (isset($values["attr"]) &&
            ! in_array(self::ATTR_OPTIONAL(), $field["attr"]) &&
            ! in_array($fieldName, self::getPK())) {
                return true;
            }
            return false;
        }

				public static function isFieldNotVisible($field) {
					$fieldDef = self::getField($field);
					return isset($fieldDef["attr"]) && in_array(self::ATTR_NOT_VISIBLE(), $fieldDef["attr"]);
				}

        public static function isEditableField($fieldName)
        {
            if (in_array($fieldName, self::getPK())) {
                return false;
            } elseif (in_array($fieldName, array_keys(self::getCommonFieldSymbols()))) {
                return false;
            } elseif (self::isFieldFK($fieldName)) {
                return false;
            }
            return true;
        }

        public static function getMandatoryFields()
        {
            $fields = self::getFields();
            $r = [];
            foreach ($fields as $fieldName => $values) {
                if (self::isFieldMandatory($fieldName)) {
                    array_push($r, $fieldName);
                }
            }
            return $r;
        }

        public function __construct($data)
        {
            $this->values = $data["values"];
        }

        public function getClassName()
        {
            return get_called_class();
        }

        public function getValues()
        {
            return $this->values;
        }

				public function getMetadata() {
					$values = $this->getValues();
					foreach ($values as $field => $value) {
						if (! self::isFieldNotVisible($field)) {
							$return[$field] = $value;
						}
					}
					return $return;
				}

        public function getValue($field)
        {
            if (self::isField($field) || (self::hasEndDate() && $field === self::STRING_FIELD_END_DATE()) || (self::hasStartDate() && $field === self::STRING_FIELD_START_DATE())) {
                return $this->getValues()[$field];
            } else {
                throw new \Bu\Exception\InvalidArgument("$field is not a valid field for " . get_called_class());
            }
        }

        public static function get($ids = null)
        {
            if (! $ids) {
                throw new \Bu\Exception\InvalidArgument("id not defined for Bu::get");
            } elseif (self::hasComposedPK() && gettype($ids) !== "array") {
                throw new \Bu\Exception\InvalidArgument("class has composed primary key - associative ids array expected");
            } elseif (self::hasSinglePK() && gettype($ids) == "array") {
                throw new \Bu\Exception\InvalidArgument("class has a single primary key - ids not expected as array");
            } elseif (self::hasComposedPK() && ! self::arraysAreEqualsUnSorted(array_keys($ids), self::getPK())) {
                throw new \Bu\Exception\InvalidArgument("missing or invalid PKs");
            }

            $class = get_called_class();
            if (self::hasSinglePK()) {
                $ids = [ self::getPK()[0] => $ids ];
            }
            $values = BuDB::getValuesSingleObject($class, $ids);

            if ($values) {
                return new $class([ "values" => $values ]);
            }

						return null;
        }

	public static function executeQuery($query, $querySymbols, $queryValues) {
		return BuDB::executeQuery($query, $querySymbols, $queryValues);
	}

        public static function find($condition = null, $queryValues = null)
        {
            if (! $condition) {
                throw new \Bu\Exception\InvalidArgument("condition not defined for Bu::find");
            }

            $class = get_called_class();
            $objects = [];
            $ids = BuDB::find($class, $condition, $queryValues);
            if ($ids) {
                foreach ($ids as $id) {
                    array_push($objects, $class::get($id));
                }
            }
            return $objects;
        }

        public static function findFirst($condition = null, $queryValues = null)
        {
            $objects = self::find($condition, $queryValues);
            if (count($objects) === 1) {
                return $objects[0];
            }
            return null;
        }

        public static function findAll()
        {
            return self::find("1 = 1");
        }

        public static function getTime()
        {
            return date("Y-m-d H:i:s");
        }

        public static function add($values = null)
        {
            if (! $values) {
                throw new \Bu\Exception\InvalidArgument("values not defined for Bu::add");
            } elseif (self::hasSinglePK() && in_array(self::getPK()[0], array_keys($values))) {
                throw new \Bu\Exception\InvalidArgument("PK cannot be set in Bu::add");
            }

            $class = get_called_class();
            if (self::hasStartDate()) {
                $values[self::STRING_FIELD_START_DATE()] = self::getTime();
            }
            $ids = BuDB::addNewObject($class, $values);

            if ($ids) {
                return $class::get($ids);
            } else {
                throw new \Bu\Exception\ErrorOnAdd();
            }
        }

        public function update($field = null, $value = null)
        {
            if (! $field || ($value === false) || ($value === null)) {
                throw new \Bu\Exception\InvalidArgument("values not defined for Bu::update");
            } elseif (! self::isField($field)) {
                throw new \Bu\Exception\InvalidArgument("field $field does not exist for Bu::update");
            }

            $class = get_called_class();
            $pks = self::getPK();
            $ids = [];
            foreach ($pks as $pk) {
                $ids[$pk] = $this->getValue($pk);
            }

            $result = BuDB::update($class, $ids, $field, $value);
            if ($result) {
                return $this->_setValue($field, $value);
            }
            return false;
        }

        public function isDeleted()
        {
            return (bool)($this->getValue(self::STRING_FIELD_END_DATE()));
        }

        public function delete()
        {
            if (! self::hasEndDate()) {
                throw new \Bu\Exception\InvalidStatus("$class cannot be deleted");
            } elseif ($this->isDeleted()) {
                throw new \Bu\Exception\InvalidStatus("object from $class is already deleted");
            }
            return $this->update(self::STRING_FIELD_END_DATE(), self::getTime());
        }

        public function _setValue($field, $value)
        {
					$this->values[$field] = $value;
					return true;
        }

        public function getObject($field)
        {
            $class = self::getFieldFKClass($field);
            return $class::get($this->getValue($field));
        }

        public static function getExternalClassFK($class)
        {
            $fields = $class::getFields();
            $fkFields = [];
            foreach ($fields as $field => $fieldDef) {
                if (isset($fieldDef["fk"]) && $fieldDef["fk"]["class"] == get_called_class()) {
                    array_push($fkFields, $field);
                }
            }
            return $fkFields;
        }

        public static function hasSingleFKReference($class)
        {
            return count(self::getExternalClassFK($class)) === 1;
        }

        public function associate($class, $values = [])
        {
            if (! self::hasSinglePK()) {
                throw new \Bu\Exception\InvalidObject("Cannot associate external class to multiple PKs class");
            } elseif (! self::hasSingleFKReference($class)) {
                throw new \Bu\Exception\InvalidObject("External class has 0 or more than 1 reference to PK");
            }
            $fkField = self::getExternalClassFK($class)[0];
            $pkValue = $this->getValue(self::getPK()[0]);
            $values = array_merge($values, [ $fkField => $pkValue ]);
            return $class::add($values);
        }

        public function findObjects($class)
        {
            $fkFields = self::getExternalClassFK($class);
            $r = [];
            foreach ($fkFields as $field) {
                $_r = $class::find("$field = ?", [ $field => $this->getValue($this->getPK()[0]) ]);
                $r = array_merge($r, $_r);
            }
            return $r;
        }
    }
