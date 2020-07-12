<?php

namespace Bu\Test;

trait Factory
{

    public $_factoryObjects = [];

    public function tearDown(): void
    {
        foreach ($this->_factoryObjects as $object) {
            if (! $object->isDeleted()) {
                $object->delete();
            }
        }
    }

    public function getConfigClass() {
        return get_called_class()::CONFIG_CLASS();
    }

    public function fillDefaultValues($class, $params) {
        $DEFAULT = $this->getPreObjectsDefinition();
        if (isset($DEFAULT[$class]) && isset($DEFAULT[$class]["values"])) {
            $defaultParams = $DEFAULT[$class]["values"];
            foreach ($defaultParams as $key => $value) {
                if (! isset($params[$key])) {
                    $params[$key] = $value;
                }
            }
        }
        return $params;
    }

    public function getConfigObject() {
        $config_class = self::getConfigClass();
        return new $config_class();
    }

    public function getPreObjectsDefinition() {
        return $this->getConfigObject()->DEFAULT();
    }

    public function getNew($class, $params = [])
    {
        if ($this->hasBaseClass()) {
            $class = $this->getBaseClass() . "\\" . $class;
        }
        if ($this->hasPreObject($class) && ! $this->hasPreObjectPredefinedKey($class, $params)) {
            $paramsPreObject = $this->getParamsPreObject($class);
            $params = array_merge($params, $paramsPreObject);
        }
        $params = $this->fillDefaultValues($class, $params);
        $this->assertEmpty($class::validate($params));
        $object = $class::add($params);
        if ($object) {
            array_push($this->_factoryObjects, $object);
            return $object;
        }
    }

    public function getBaseClass() {
        return $this->getConfigObject()->BASE_CLASS();
    }

    public function hasBaseClass() {
        return method_exists($this->getConfigObject(), "BASE_CLASS");
    }

    public function hasPreObject($class)
    {
        return isset($this->getPreObjectsDefinition()[$class]);
    }

    public function hasPreObjectPredefinedKey($class, $params)
    {
        $def = $this->getPreObjectsDefinition()[$class];
        $required = $def["key"];
        if (isset($def["key"])) {
            return (count(array_intersect_key(array_flip($required), $params)) === count($required));
        }
    }

    public function getParamsPreObject($class)
    {
        $preObjects = $this->getPreObjectsDefinition();
        if (isset($preObjects[$class]["function"])) {
            return $preObjects[$class]["function"]();
        }
        return [];
    }
}
