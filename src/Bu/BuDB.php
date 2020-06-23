<?php

	namespace Bu;

	use Bu\Bu;
	
	class BuDB extends Bu {

        private static $HOST = "localhost";
        private static $USER = "root";
        private static $PASS = ""; 
        private static $DBNAME = "base";

        private static function getHost() { return self::$HOST; }
        private static function getUser() { return self::$USER; }
        private static function getPass() { return self::$PASS; }
        private static function getDbname() { return self::$DBNAME; }

        private static function getConex() {
            $conex = new \mysqli(self::getHost(), self::getUser(), self::getPass(), self::getDbname());
            if ($conex->connect_error) {
                throw new \Bu\Exception\DBConnectionError($conex->connect_error);
            }
            return $conex;
        }

        public static function addNewObject($class, $values) {
            $table = $class::getTable();
            $query = "insert into $table (" . implode(",", array_keys($values)) . ") values (" . implode(",", array_fill(0, count(array_keys($values)), "?")) . ")";
            
            $parsedValues = [];
            $querySymbols = [];

            foreach ($values as $key => $value) {
                $symbol = $class::getFieldSymbol($key);
                array_push($parsedValues, $value);
                array_push($querySymbols, $symbol);
            }

            $conex = self::getConex();
            if ($conex) {
                $st = $conex->prepare($query);
                if ($st) {
                   $st->bind_param(implode("", $querySymbols), ...$parsedValues);
                   if ($st->execute()) {
                       if ($class::hasSinglePK()) {
                           return $conex->insert_id;
                       } else {
                            $pks = $class::getPK();
                            $r = [];
                            foreach ($pks as $pk) {
                                $r[$pk] = $values[$pk];
                            }
                            return $r;
                       }
                       
                       $result = $st->get_result();
                       if ($result->num_rows === 1) {
                           return $result->fetch_assoc();
                       }
                    } else {
                        throw new \Bu\Exception\DBStatementError($st->error); 
                    }
                } else {
                    throw new \Bu\Exception\DBStatementError($conex->error);
                }
            }
            return false;
        }

        public static function getValuesSingleObject($class, $ids) {
            $fieldNames = $class::getFieldNames();
            $parsedFields = implode(",", $fieldNames);
            $table = $class::getTable();
            $pks = $class::getPK();

            $query = "select $parsedFields from $table where ";
            $conditions = [];
            $parsedValues = [];
            $querySymbols = [];

            foreach ($pks as $pk) {
                array_push($conditions, "$pk = ?");

                $symbol = $class::getFieldSymbol($pk);
                $value = $ids[$pk];
                array_push($querySymbols, $symbol);
                array_push($parsedValues, $value);
            }
            $query .= implode(" and ", $conditions);

            $conex = self::getConex();
            if ($conex) {
                $st = $conex->prepare($query);
                if ($st) {
                   $st->bind_param(implode("", $querySymbols), ...$parsedValues);
                   if ($st->execute()) {
                       $result = $st->get_result();
                       if ($result->num_rows === 1) {
                           return $result->fetch_assoc();
                       }
                   }
                } else {
                    throw new \Bu\Exception\DBStatementError($conex->error);
                }
            }

            return false;
        }

    }

?>