<?php

    namespace Bu;

    class API extends Bu
    {
        use APIActions;
        use APIConst;
        use APIResponse;

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
                $parameters = $this->fillDefaultValues($classname, $parameters);
                return $this->getActionFunction($action)($classname, $parameters);
            }
        }

        public function fillDefaultValues($classname, $parameters)
        {
            $defaultValues = $classname::getAPIDefaultValues($this->getUser());
            return array_merge($parameters, $defaultValues);
        }

        public function getResponseError($code, $msg = null)
        {
            return $this->getResponse(self::API_STATUS_ERROR(), $code, $msg);
        }

        public function getResponseSuccess($msg)
        {
            return $this->getResponse(self::API_STATUS_SUCCESS(), null, $msg);
        }

        public function getResponse($status, $code = null, $msg)
        {
            $data = [ "status" => $status ];
            if ($code) {
                $data["code"] = $code;
            }
            if ($msg) {
                $data["data"] = $msg;
            }
            return $data;
        }

        public static function run($method, $parameters, $output = null)
        {
            if (! $output) {
                $output = self::API_OUTPUT_JSON();
            }
        }
    }
