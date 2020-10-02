<?php

    namespace Bu;

    class API extends Bu
    {
        public static function call($method, $parameters = [])
        {
            $APIClass = get_called_class();
            return new $APIClass($method, $parameters);
        }

        public static function API_ERROR_INVALID_CLASSNAME()
        {
            return "API_ERROR_INVALID_CLASSNAME";
        }

        public static function API_ERROR_INVALID_ACTION()
        {
            return "API_ERROR_INVALID_ACTION";
        }

        public static function API_ERROR_INVALID_PARAMETERS()
        {
            return "API_ERROR_INVALID_PARAMETERS";
        }

        public static function API_OUTPUT_JSON()
        {
            return "json";
        }

        public static function ACTION_ADD()
        {
            return "add";
        }
        public static function ACTION_DEL()
        {
            return "del";
        }
        public static function ACTION_VIEW()
        {
            return "view";
        }
        public static function ACTION_MODIFY()
        {
            return "modify";
        }

        public static function VALID_ACTIONS()
        {
            return [
            self::ACTION_ADD(),
            self::ACTION_DEL(),
            self::ACTION_VIEW(),
            self::ACTION_MODIFY()
          ];
        }

        public static function API_MAP()
        {
            return [];
        }

        public function __construct($method, $parameters)
        {
            $this->method = $method;
            $this->parameters = $parameters;
            $this->errors = [];

            $this->setErrors();
            if (! $this->hasErrors()) {
                $this->parameters = json_decode($parameters, true);
            }
        }

        public function getMethod()
        {
            return $this->method;
        }

        public function getParameters()
        {
            return $this->parameters;
        }

        public function getParsedMethod()
        {
            $data = explode("/", $this->getMethod());
            return [
              isset($data[0]) ? $data[0] : "",
              isset($data[1]) ? $data[1] : ""
            ];
        }

        public function getClassKey()
        {
            return $this->getParsedMethod()[0];
        }

        public function getAction()
        {
            return $this->getParsedMethod()[1];
        }

        public static function isValidAction($action = "")
        {
            return in_array($action, self::VALID_ACTIONS());
        }

        public static function getAPIMap()
        {
            return get_called_class()::API_MAP();
        }

        public static function isValidClassKey($classkey = "")
        {
            return array_key_exists($classkey, self::getAPIMap());
        }

        public static function isValidParameters($parameters = null)
        {
            if ($parameters) {
                return \Bu\Validate::validateType(\Bu\Validate::VALIDATE_TYPE_JSON(), $parameters);
            }
            return false;
        }

        public function hasErrors()
        {
            return (bool)count($this->getErrors());
        }

        public function getErrors()
        {
            return $this->errors;
        }

        public function setErrors()
        {
            if (! self::isValidClassKey($this->getClassKey())) {
                array_push($this->errors, self::API_ERROR_INVALID_CLASSNAME());
            }
            if (! self::isValidAction($this->getAction())) {
                array_push($this->errors, self::API_ERROR_INVALID_ACTION());
            }
            if (! self::isValidParameters($this->getParameters())) {
                array_push($this->errors, self::API_ERROR_INVALID_PARAMETERS());
            }
        }

        public function getClassName()
        {
            return self::getAPIMap()[$this->getClassKey()];
        }

        public function execute()
        {
            if (! $this->hasErrors()) {
                return $this->executeMethod($this->getClassName(), $this->getAction(), $this->getParameters());
            }
        }

        public function getActionFunction($action)
        {
            $ACTION_FUNCTION = [
              self::ACTION_ADD() => function ($classname, $parameters) {
              },
              self::ACTION_DEL() => function ($classname, $parameters) {
              },
              self::ACTION_VIEW() => function ($classname, $parameters) {
                  if ($classname::hasSinglePK()) {
                      $pkValue = $parameters[$classname::getPK()[0]];
                  } else {
                      $pkValue = array_filter($parameters, function ($key, $value) {
                          if (in_array($key, $classname::getPK())) {
                              return [ $key => $value ];
                          }
                      }, ARRAY_FILTER_USE_BOTH);
                  }
                  $object = $classname::get($pkValue);
                  return $object->getValues();
              },
              self::ACTION_MODIFY() => function ($classname, $parameters) {
              }
            ];
            return $ACTION_FUNCTION[$action];
        }

        public function executeMethod($classname, $action, $parameters)
        {
            return $this->getActionFunction($action)($classname, $parameters);
        }

        public static function run($method, $parameters, $output = null)
        {
            if (! $output) {
                $output = self::API_OUTPUT_JSON();
            }
        }
    }
