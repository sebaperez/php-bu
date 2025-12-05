<?php

namespace Bu;

use Bu\Bu;

class BuDBSQLServer extends Bu
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
    public static function getDefaultDBname()
    {
        return self::$DBNAME;
    }

    public static function getFullSchema($class = null) {
	$dbName = self::getDBName($class);
	$schema = self::getSchema($class);

	return $dbName . ($schema ? "." . $schema : "");
    }

    public static function getDBName($class = null) {
	if ($class && $class::getSelfConfig()) {
		return $class::getSelfConfigValue("DBNAME");
	} else {
		return isset($GLOBALS["DBNAME"]) ? $GLOBALS["DBNAME"] : self::getDefaultDBname();
	}
    }

    public static function getSchema($class = null) {
	if ($class && $class::getSelfConfig() && $class::getSelfConfigValue("SCHEMA")) {
		return $class::getSelfConfigValue("SCHEMA");
	}
    }

    private static function getConex($class = null)
    {
	if ($class && $class::getSelfConfig()) {
		$host = $class::getSelfConfigValue("DBHOST");
		$user = $class::getSelfConfigValue("DBUSER");
		$pass = $class::getSelfConfigValue("DBPASS");
	} else {
		$host = isset($GLOBALS["DBHOST"]) ? $GLOBALS["DBHOST"] : self::getDBHost();
		$user = isset($GLOBALS["DBUSER"]) ? $GLOBALS["DBUSER"] : self::getDBUser();
		$pass = isset($GLOBALS["DBPASS"]) ? $GLOBALS["DBPASS"] : self::getDBPass();
	}

	$dbname = self::getDBName($class);

	$conex = sqlsrv_connect($host, [
		"Database" => $dbname,
		"UID" => $user,
		"PWD" => $pass
	]);
        if (! $conex) {
	    $errors = sqlsrv_errors();
            throw new \Bu\Exception\DBConnectionError((isset($errors) && isset($errors[0]) && isset($errors[0]["message"])) ? $errors[0]["message"] : "");
        }
        return $conex;
    }

    public static function addNewObject($class, $values)
    {
        $table = $class::getTable();
        $query = "insert into " . self::getFullSchema($class) . "." . "$table (" . implode(",", array_keys($values)) . ") values (" . implode(",", array_fill(0, count(array_keys($values)), "?")) . ")";

        $parsedValues = [];

        foreach ($values as $key => $value) {
            $symbol = $class::getFieldSymbol($key);
            array_push($parsedValues, $value);
        }

        $conex = self::getConex($class);
        if ($conex) {
	    $query .= "; SELECT SCOPE_IDENTITY()";
	    $st = sqlsrv_prepare($conex, $query, $parsedValues);
            if ($st) {
                if (sqlsrv_execute($st)) {
                    if ($class::hasSinglePK()) {
			sqlsrv_next_result($st);
			sqlsrv_fetch($st);
			return sqlsrv_get_field($st, 0);
                    } else {
                        $pks = $class::getPK();
                        $r = [];
                        foreach ($pks as $pk) {
                            $r[$pk] = $values[$pk];
                        }
                        return $r;
                    }
                } else {
                    throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
                }
            } else {
                throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
            }
        }
        return false;
    }

    public static function update($class, $ids, $field, $value)
    {
        $table = $class::getTable();

        $query = "update " . self::getFullSchema($class) . "." . "$table set $field = ? where ";
        $conditions = [];
        $parsedValues = [$value];

        foreach ($ids as $pk => $pkValue) {
            array_push($conditions, "$pk = ?");
            $symbol = $class::getFieldSymbol($pk);
            array_push($parsedValues, $pkValue);
        }
        if ($class::hasEndDate() && ! $class::isLoadableIfDeleted()) {
            array_push($conditions, $class::STRING_FIELD_END_DATE() . " is null");
        }
        $query .= implode(" and ", $conditions);
        $conex = self::getConex($class);
        if ($conex) {
	    $st = sqlsrv_prepare($conex, $query, $parsedValues);
            if ($st) {
                if (sqlsrv_execute($st)) {
                    return true;
                }
            } else {
                throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
            }
        }

        return false;
    }

	public static function truncate($table) {
		$conex = self::getConex();
		if ($conex) { 
			$st = $conex->prepare("truncate table " . self::getFullSchema($class) . "." . $table);
			return $st->execute();
		} else {
			throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
		}
	}

    public static function executeQuery($query, $querySymbols = null, $queryValues, $class = null) {
	$conex = self::getConex($class);
	if ($conex) {
		$parsedValues = [];
		if ($queryValues) {
			foreach ($queryValues as $fieldName => $value) {
				array_push($parsedValues, $value);
			}
		}
		$st = sqlsrv_prepare($conex, $query, $parsedValues);
		if ($st) {
			if (sqlsrv_execute($st)) {
				$r = [];
				while ($data = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
					array_push($r, $data);
				}
				return $r;
			}
		}
	} else {
		throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
	}
    }

    public static function hasSimpleLoad() {
	return \Bu\Base::hasSimpleLoad();
    }

    public static function find($class, $condition, $queryValues)
    {
	if (self::hasSimpleLoad()) {
		$fieldNames = $class::getFieldNames();
	} else {
		$fieldNames = $class::getPK();
	}
        $parsedFields = implode(",", $fieldNames);
        $table = $class::getTable();

        $query = "select $parsedFields from " . self::getFullSchema($class) . "." . "$table where";
	if ($class::hasEndDate() && ! $class::isLoadableIfDeleted()) {
		$query .= " " . $class::STRING_FIELD_END_DATE() . " is null and";
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

        $conex = self::getConex($class);
        if ($conex) {
	    $st = sqlsrv_prepare($conex, $query, $parsedValues);
            if ($st) {
		$r = [];
                if (sqlsrv_execute($st)) {
                    while ($data = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
			if (self::hasSimpleLoad()) {
			    $id = $data;
			} else if ($class::hasSinglePK()) {
                            $id = $data[$class::getPK()[0]];
                        } else {
                            $id = $data;
                        }
                        array_push($r, $id);
                    }
                    return $r;
                }
            } else {
                throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
            }
        }
    }

    public static function getValuesSingleObject($class, $ids)
    {
        $fieldNames = $class::getFieldNames();
        $parsedFields = implode(",", $fieldNames);
        $table = $class::getTable();
        $pks = $class::getPK();

        $query = "select $parsedFields from " . self::getFullSchema($class) . "." . "$table where ";
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
            array_push($conditions, $class::STRING_FIELD_END_DATE() . " is null");
        }
        $query .= implode(" and ", $conditions);

        $conex = self::getConex($class);
        if ($conex) {
            $st = sqlsrv_prepare($conex, $query, $parsedValues);
            if ($st) {
                if (sqlsrv_execute($st)) {
                    $data = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC);
                    return $data;
                }
            } else {
                throw new \Bu\Exception\DBStatementError(json_encode(sqlsrv_errors()));
            }
        }

        return false;
    }
}
