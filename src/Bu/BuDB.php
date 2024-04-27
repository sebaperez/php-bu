<?php

namespace Bu;

use Bu\Bu;

class BuDB extends Bu
{
    private static $HOST = "localhost";
    private static $USER = "root";
    private static $PASS = "";
    private static $DBNAME = "base";

    public static function getDBHost()
    {
        return self::$HOST;
    }
    public static function getDBUser()
    {
        return self::$USER;
    }
    public static function getDBPass()
    {
        return self::$PASS;
    }
    public static function getDBname()
    {
        return self::$DBNAME;
    }

    private static function getConex()
    {
				$host = isset($GLOBALS["DBHOST"]) ? $GLOBALS["DBHOST"] : self::getDBHost();
				$user = isset($GLOBALS["DBUSER"]) ? $GLOBALS["DBUSER"] : self::getDBUser();
				$pass = isset($GLOBALS["DBPASS"]) ? $GLOBALS["DBPASS"] : self::getDBPass();
				$dbname = isset($GLOBALS["DBNAME"]) ? $GLOBALS["DBNAME"] : self::getDBname();

        $conex = new \mysqli($host, $user, $pass, $dbname);
        if ($conex->connect_error) {
            throw new \Bu\Exception\DBConnectionError($conex->connect_error);
        }
        return $conex;
    }

    public static function addNewObject($class, $values)
    {
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

    public static function update($class, $ids, $field, $value)
    {
        $table = $class::getTable();

        $query = "update $table set $field = ? where ";
        $conditions = [];
        $parsedValues = [$value];
        $querySymbols = [$class::getFieldSymbol($field)];

        foreach ($ids as $pk => $pkValue) {
            array_push($conditions, "$pk = ?");
            $symbol = $class::getFieldSymbol($pk);
            array_push($querySymbols, $symbol);
            array_push($parsedValues, $pkValue);
        }
        if ($class::hasEndDate() && ! $class::isLoadableIfDeleted()) {
            array_push($conditions, "end_date is null");
        }
        $query .= implode(" and ", $conditions);
        $conex = self::getConex();
        if ($conex) {
            $st = $conex->prepare($query);
            if ($st) {
                $st->bind_param(implode("", $querySymbols), ...$parsedValues);
                if ($st->execute()) {
                    return ($st->affected_rows === 1);
                }
            } else {
                throw new \Bu\Exception\DBStatementError($conex->error);
            }
        }

        return false;
    }

    public static function executeQuery($query, $querySymbols, $queryValues) {
	$conex = self::getConex();
	if ($conex) {
		$st = $conex->prepare($query);
		if ($st) {
			$st->bind_param($querySymbols, ...$queryValues);
			if ($st->execute()) {
				$result = $st->get_result();
				$r = [];
				while ($data = $result->fetch_assoc()) {
					array_push($r, $data);
				}
				return $r;
			}
		}
	} else {
		throw new \Bu\Exception\DBStatementError($conex->error);
	}
    }

    public static function find($class, $condition, $queryValues)
    {
        $fieldNames = $class::getPK();
        $parsedFields = implode(",", $fieldNames);
        $table = $class::getTable();

        $query = "select $parsedFields from $table where";
	if ($class::hasEndDate()) {
		$query .= " end_date is null and";
	}
	$query .= " $condition";
        $querySymbols = [];
        $parsedValues = [];

        if ($queryValues) {
            foreach ($queryValues as $fieldName => $value) {
                $symbol = $class::getFieldSymbol($fieldName);
                array_push($querySymbols, $symbol);
                array_push($parsedValues, $value);
            }
        }
        if ($class::hasEndDate() && ! $class::isLoadableIfDeleted()) {
            $query .= " and end_date is null";
        }

        $conex = self::getConex();
        if ($conex) {
            $st = $conex->prepare($query);
            if ($st) {
                if ($queryValues) {
                    $st->bind_param(implode("", $querySymbols), ...$parsedValues);
                }
                if ($st->execute()) {
                    $result = $st->get_result();
                    $r = [];
                    while ($data = $result->fetch_assoc()) {
                        if ($class::hasSinglePK()) {
                            $id = $data[$class::getPK()[0]];
                        } else {
                            $id = $data;
                        }
                        array_push($r, $id);
                    }
                    return $r;
                }
            } else {
                throw new \Bu\Exception\DBStatementError($conex->error);
            }
        }
    }

    public static function getValuesSingleObject($class, $ids)
    {
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
        if ($class::hasEndDate() && ! $class::isLoadableIfDeleted()) {
            array_push($conditions, "end_date is null");
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
