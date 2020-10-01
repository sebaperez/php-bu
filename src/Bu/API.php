<?php

    namespace Bu;

    class API extends Bu
    {
        public static function call($method, $parameters = [])
        {
            return new API($method, $parameters);
        }

        public function __construct($method, $parameters)
        {
            $this->method = $method;
            $this->parameters = $parameters;
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
