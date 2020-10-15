<?php

    namespace Bu;

    class API extends Bu
    {
        public static function API_ERROR_INVALID_METHOD()
        {
            return "ERROR_INVALID_METHOD";
        }

        public static function get($method, $parameters, $session = null)
        {
            $class = get_called_class();
            return new $class($method, $parameters, $session);
        }

        public function __construct($method, $parameters, $session = null)
        {
            $this->method = $method;
            $this->parameters = $parameters;
            $this->session = $session;

            $this->error = null;
        }

        public function getMethods()
        {
            return [];
        }

        public function getMethod()
        {
            return $this->method;
        }

        public function isValidMethod()
        {
            return isset($this->getMethods()[$this->getMethod()]);
        }

        public function getParameters()
        {
            return $this->parameters;
        }

        public function setError($errorCode)
        {
            return $this->error = [
              "errorCode" => $errorCode
            ];
        }

        public function hasError()
        {
            return (bool)$this->getError();
        }

        public function getError()
        {
            return $this->error;
        }

        public function getMessage()
        {
            if ($this->hasError()) {
                return [
                  "status" => "error",
                  "message" => $this->getError()
                ];
            }
        }

        public function execute()
        {
            if ($this->isValidMethod()) {
            } else {
                $this->setError(self::API_ERROR_INVALID_METHOD());
                return false;
            }
        }
    }
