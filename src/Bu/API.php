<?php

    namespace Bu;

    class API extends Bu
    {
        public static function API_ERROR_INVALID_METHOD()
        {
            return "ERROR_INVALID_METHOD";
        }
				public static function API_ERROR_FORBIDDEN()
				{
						return "API_ERROR_FORBIDDEN";
				}
				public static function API_ERROR_MANDATORY_PARAMS_MISSING() {
					return "API_ERROR_MANDATORY_PARAMS_MISSING";
				}
				public static function API_ERROR_LOGIN_WRONG_CREDENTIALS() {
					return "API_ERROR_LOGIN_WRONG_CREDENTIALS";
				}
				public static function API_ERROR_VALIDATION() {
					return "API_ERROR_VALIDATION";
				}
				public static function API_UNKNOWN_ERROR() {
					return "API_UNKNOWN_ERROR";
				}

				public static function API_ATTRIBUTE_NO_REQUIRES_LOGIN() {
					return "API_ATTRIBUTE_NO_REQUIRES_LOGIN";
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
						$this->user = null;

						if ($this->session) {
							$session = get_called_class()::SESSION_CLASS()::getByHash($this->session);
							if ($session) {
								$this->user = $session->getUser();
							}
						}

            $this->error = null;
						$this->ok = null;
        }

				public function getUser() {
					return $this->user;
				}

				public function hasUser() {
					return (bool)$this->getUser();
				}

				public function getSession() {
					return get_called_class()::SESSION_CLASS()::getByHash($this->session);
				}

        public function getMethods()
        {
            return [
							"user/login" => [
								"attr" => [ self::API_ATTRIBUTE_NO_REQUIRES_LOGIN() ],
								"mandatoryParams" => [ "email", "password" ],
								"function" => function($params) {
									$session = get_called_class()::USER_CLASS()::getNewSession($params["email"], $params["password"]);
									if ($session) {
										return $this->setOK([
											"session" => $session->getValue("hash")
										]);
									} else {
										return $this->setError(self::API_ERROR_LOGIN_WRONG_CREDENTIALS());
									}
								}
							],
							"user/logout" => [
								"function" => function() {
									$session = $this->getSession();
									if ($session && $session->delete()) {
										return $this->setOK();
									} else {
										return $this->setError(self::API_ERROR_FORBIDDEN());
									}
								}
							],
							"user/current" => [
								"function" => function() {
									return $this->setOK($this->getUser()->getMetadata());
								}
							],
							"user/list" => [
								"function" => function() {
									if ($this->getUser()->can("MANAGE_USERS")) {
										$account = $this->getUser()->getObject("account_id");
										$users = $account->findObjects(get_called_class()::USER_CLASS());
										return $this->setOK(array_map(function($user) {
											return $user->getMetadata();
										}, $users));
									} else {
										return $this->setError(self::API_ERROR_FORBIDDEN());
									}
								}
							],
							"user/add" => [
								"preParameters" => function($params) {
									$params["account_id"] = $this->getUser()->getValue("account_id");
									$params["password"] = $this->getRandomString();
									return $params;
								},
								"mandatoryParams" => get_called_class()::USER_CLASS()::getMandatoryFields(),
								"function" => function($params) {
									if ($this->getUser()->can("MANAGE_USERS")) {
										$validation = get_called_class()::USER_CLASS()::validate($params);
										if (! $validation) {
											if ($user = get_called_class()::USER_CLASS()::add($params)) {
												return $this->setOK($user->getMetadata());
											} else {
												return $this->setError(self::API_UNKNOWN_ERROR());
											}
											return $this->setOK();
										} else {
											return $this->setError(self::API_ERROR_VALIDATION(), $validation);
										}
									} else {
										return $this->setError(self::API_ERROR_FORBIDDEN());
									}
								}
							]
						];
        }

        public function getMethod()
        {
            return $this->method;
        }

        public function isValidMethod()
        {
            return isset($this->getMethods()[$this->getMethod()]);
        }

				public function isLoginRequired() {
					return ! $this->hasAttribute(self::API_ATTRIBUTE_NO_REQUIRES_LOGIN());
				}

				public function getMethodDefinition() {
					return $this->getMethods()[$this->getMethod()];
				}

				public function getFunction() {
					return $this->getMethodDefinition()["function"];
				}

				public function getPreParameters() {
					return $this->getMethodDefinition()["preParameters"];
				}

				public function hasPreParameters() {
					return isset($this->getMethodDefinition()["preParameters"]);
				}

				public function getMethodAttributes() {
					return isset($this->getMethodDefinition()["attr"]) ? $this->getMethodDefinition()["attr"] : [];
				}

				public function hasAttribute($attribute) {
					return in_array(self::API_ATTRIBUTE_NO_REQUIRES_LOGIN(), $this->getMethodAttributes());
				}

				public function hasMandatoryParams() {
					return isset($this->getMethodDefinition()["mandatoryParams"]);
				}

				public function getMandatoryParams() {
					return $this->getMethodDefinition()["mandatoryParams"];
				}

				public function getMandatoryParamsMissed() {
					return array_diff($this->getMandatoryParams(), array_keys($this->getParameters()));
				}

				public function isMandatoryParamsMissing() {
					return count($this->getMandatoryParamsMissed()) > 0;
				}

        public function getParameters()
        {
            return $this->parameters;
        }

        public function setError($errorCode, $description = null)
        {
            $this->error = [ "errorCode" => $errorCode ];
						if ($description) {
							$this->error["description"] = $description;
						}
						return false;
        }

				public function setOK($message = null) {
					$this->error = null;
					if ($message) {
						$this->ok["message"] = $message;
					}
					return true;
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
            } else {
							$return = [
								"status" => "success"
							];
							if ($this->ok && isset($this->ok["message"])) {
								$return["message"] = $this->ok["message"];
							}
							return $return;
						}
        }

        public function execute()
        {
            if (! $this->isValidMethod()) {
							$this->setError(self::API_ERROR_INVALID_METHOD());
							return false;
            } else if ($this->isLoginRequired() && ! $this->hasUser()) {
							$this->setError(self::API_ERROR_FORBIDDEN());
							return false;
            }

						if ($this->hasPreParameters()) {
								$this->parameters = $this->getPreParameters()($this->getParameters());
						}

						if ($this->hasMandatoryParams() && $this->isMandatoryParamsMissing()) {
							$this->setError(self::API_ERROR_MANDATORY_PARAMS_MISSING(), $this->getMandatoryParamsMissed());
							return false;
						} else {
							$function = $this->getFunction();
							return $function($this->getParameters());
						}
        }
    }
