<?php

    namespace Bu;

    class API extends Bu
    {
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
        }

        public function getMethod()
        {
            return $this->method;
        }

        public function getParameters()
        {
            return $this->parameters;
        }
    }
