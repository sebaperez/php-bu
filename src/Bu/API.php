<?php

    namespace Bu;

    class API extends Bu
    {
        public static function call($method, $parameters = [], $sessionHash = null)
        {
            $APIClass = get_called_class();
            $sessionClass = $APIClass::SESSION_CLASS();
            $session = $sessionClass::getByHash($sessionHash);
            $currentUser = null;
            if ($session) {
                $currentUser = $session->getUser();
            }
            return new $APIClass($method, $parameters, $currentUser);
        }

        public static function API_STATUS_ERROR()
        {
            return "error";
        }

        public static function API_STATUS_SUCCESS()
        {
            return "success";
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

        public static function API_ERROR_FORBIDDEN()
        {
            return "API_ERROR_FORBIDDEN";
        }

        public static function API_ERROR_INTERNAL_ERROR()
        {
            return "API_ERROR_INTERNAL_ERROR";
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
        public static function ACTION_EDIT()
        {
            return "edit";
        }
        public static function ACTION_LIST()
        {
            return "list";
        }

        public static function VALID_ACTIONS()
        {
            return [
            self::ACTION_ADD(),
            self::ACTION_DEL(),
            self::ACTION_VIEW(),
            self::ACTION_EDIT(),
            self::ACTION_LIST()
          ];
        }

        public static function API_MAP()
        {
            return [];
        }

        public static function SESSION_CLASS()
        {
            return "Bu\DefaultClass\Session";
        }
        public static function USER_CLASS()
        {
            return "Bu\DefaultClass\User";
        }
        public static function ACCOUNT_CLASS()
        {
            return "Bu\DefaultClass\Account";
        }

        public function __construct($method, $parameters, $currentUser = null)
        {
            $this->method = $method;
            $this->parameters = $parameters;
            $this->errors = [];
            $this->currentUser = $currentUser;

            if (! $this->hasError()) {
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

        public function getUser()
        {
            return $this->currentUser;
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

        public function hasError()
        {
            return (bool)$this->getError();
        }

        public function getError()
        {
            if (! self::isValidClassKey($this->getClassKey())) {
                return self::API_ERROR_INVALID_CLASSNAME();
            }
            if (! self::isValidAction($this->getAction())) {
                return self::API_ERROR_INVALID_ACTION();
            }
            if (! self::isValidParameters($this->getParameters())) {
                return self::API_ERROR_INVALID_PARAMETERS();
            }
            return false;
        }

        public function getClassName()
        {
            return self::getAPIMap()[$this->getClassKey()];
        }

        public function execute()
        {
            if (! $this->hasError()) {
                return $this->executeMethod($this->getClassName(), $this->getAction(), $this->getParameters());
            } else {
                return $this->getResponseError($this->getError());
            }
        }

        public function actionAffectsExistingObject($action)
        {
            return $action !== self::ACTION_ADD();
        }

        public function getObject($classname, $parameters)
        {
            if ($classname::hasSinglePK()) {
                $pkValue = $parameters[$classname::getPK()[0]];
            } else {
                $pkValue = array_filter($parameters, function ($key, $value) {
                    if (in_array($key, $classname::getPK())) {
                        return [ $key => $value ];
                    }
                }, ARRAY_FILTER_USE_BOTH);
            }
            return $classname::get($pkValue);
        }

        public static function isClassOwnedByUser($classname)
        {
            return $classname::isOwnedBy(get_called_class()::USER_CLASS());
        }

        public static function isClassOwnedByAccount($classname)
        {
            return $classname::isOwnedBy(get_called_class()::ACCOUNT_CLASS());
        }

        public function hasUserAccessToObject($object)
        {
            $classname = $object->getClassName();
            $ownerField = $classname::getOwnerField();
            if (self::isClassOwnedByUser($classname)) {
                return $object->getValue($ownerField) === $this->getUser()->getValue($ownerField);
            } elseif (self::isClassOwnedByAccount($classname)) {
                return $object->getValue($ownerField) === $this->getUser()->getObject("account_id")->getValue($ownerField);
            } elseif ($ownerField) {
                return self::hasUserAccessToObject($object->getObject($ownerField));
            }
            return false;
        }

        public function executeMethod($classname, $action, $parameters)
        {
            if ($this->actionAffectsExistingObject($action)) {
                $object = $this->getObject($classname, $parameters);
                if ($object && $this->hasUserAccessToObject($object)) {
                    return $this->getActionFunction($action)($classname, $parameters);
                } else {
                    return $this->getResponseError(self::API_ERROR_FORBIDDEN());
                }
            } else {
                return $this->getActionFunction($action)($classname, $parameters);
            }
        }

        public function getResponseError($msg)
        {
            return $this->getResponse(self::API_STATUS_ERROR(), $msg);
        }

        public function getResponseSuccess($msg)
        {
            return $this->getResponse(self::API_STATUS_SUCCESS(), $msg);
        }

        public function getResponse($status, $msg)
        {
            return [
              "status" => $status,
              "data" => $msg
          ];
        }

        public static function run($method, $parameters, $output = null)
        {
            if (! $output) {
                $output = self::API_OUTPUT_JSON();
            }
        }

        public function getActionFunction($action)
        {
            $ACTION_FUNCTION = [
              self::ACTION_ADD() => function ($classname, $parameters) {
                  $validationErrors = $classname::validate($parameters);
                  if (count($validationErrors) === 0) {
                      return $this->getResponseError($validationErrors);
                  } else {
                      $object = $classname::add($parameters);
                      if ($object) {
                          return $this->getResponseSuccess($object->getValues());
                      } else {
                          return $this->getResponseError(self::API_ERROR_INTERNAL_ERROR());
                      }
                  }
              },
              self::ACTION_DEL() => function ($classname, $parameters) {
              },
              self::ACTION_VIEW() => function ($classname, $parameters) {
                  $object = $this->getObject($classname, $parameters);
                  if ($object) {
                      return $this->getResponseSuccess($object->getValues());
                  } else {
                      return $this->getResponseError(self::API_ERROR_FORBIDDEN());
                  }
              },
              self::ACTION_EDIT() => function ($classname, $parameters) {
              },
              self::ACTION_LIST() => function ($classname, $parameters) {
              }
            ];

            return $ACTION_FUNCTION[$action];
        }
    }
