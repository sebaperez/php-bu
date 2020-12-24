<?php

    namespace Bu;

    trait Validate
    {

        // Validation attributes
        public static function VALIDATE_TYPE_EMAIL()
        {
            return "email";
        }
        public static function VALIDATE_TYPE_URL()
        {
            return "url";
        }
        public static function VALIDATE_TYPE_DOMAIN()
        {
            return "domain";
        }
        public static function VALIDATE_TYPE_STRING()
        {
            return "string";
        }
        public static function VALIDATE_TYPE_INT()
        {
            return "int";
        }
        public static function VALIDATE_TYPE_NUMBER()
        {
            return "number";
        }
        public static function VALIDATE_TYPE_DATE()
        {
            return "date";
        }
        public static function VALIDATE_TYPE_DATETIME()
        {
            return "datetime";
        }
        public static function VALIDATE_TYPE_TIME()
        {
            return "time";
        }
        public static function VALIDATE_TYPE_HOUR_MINUTE()
        {
            return "hourminute";
        }
        public static function VALIDATE_MIN_LENGTH()
        {
            return "min_length";
        }
        public static function VALIDATE_MAX_LENGTH()
        {
            return "max_length";
        }
        public static function VALIDATE_TYPE_JSON()
        {
            return "json";
        }

        // Validation errors
        public static function VALIDATE_ERROR_MISSING_FIELD()
        {
            return "ERROR_MISSING_FIELD";
        }
        public static function VALIDATE_ERROR_TYPE()
        {
            return "ERROR_TYPE";
        }
        public static function VALIDATE_ERROR_LENGTH()
        {
            return "ERROR_LENGTH";
        }
        public static function VALIDATE_ERROR_DATE()
        {
            return "ERROR_DATE";
        }
        public static function VALIDATE_ERROR_FORBIDDEN()
        {
            return "FORBIDDEN";
        }

        public static function setError($response, $field, $error, $details = null)
        {
            if (! isset($response[$field])) {
                $response[$field] = [
                    "error" => $error
                ];
                if ($details) {
                    $response[$field]["details"] = $details;
                }
            }
            return $response;
        }


        public static function hasValidate($field)
        {
            $fieldDef = self::getField($field);
            return $fieldDef && isset($fieldDef["validate"]);
        }

        public static function hasValidateType($field)
        {
            return self::hasValidate($field) && isset(self::getField($field)["validate"]["type"]);
        }

        public static function getValidateType($field)
        {
            return self::getField($field)["validate"]["type"];
        }

        public static function hasValidateLength($field)
        {
            return (self::hasValidateMinLength($field) || self::hasValidateMaxLength($field));
        }

        public static function hasValidateMinLength($field)
        {
            return isset(self::getField($field)["validate"]["min_length"]);
        }

        public static function hasValidateMaxLength($field)
        {
            return isset(self::getField($field)["validate"]["max_length"]);
        }

        public static function getValidateMinLength($field)
        {
            if (self::hasValidateMinLength($field)) {
                return self::getField($field)["validate"]["min_length"];
            }
            return false;
        }

        public static function getValidateMaxLength($field)
        {
            if (self::hasValidateMaxLength($field)) {
                return self::getField($field)["validate"]["max_length"];
            }
            return false;
        }

        public static function hasValidateDateMin($field)
        {
            return isset(self::getField($field)["validate"]["min_date"]);
        }

        public static function hasValidateDateMax($field)
        {
            return isset(self::getField($field)["validate"]["max_date"]);
        }

        public static function getValidateDateMin($field)
        {
            if (self::hasValidateDateMin($field)) {
                return self::getField($field)["validate"]["min_date"];
            }
            return false;
        }

        public static function getValidateDateMax($field)
        {
            if (self::hasValidateDateMax($field)) {
                return self::getField($field)["validate"]["max_date"];
            }
            return false;
        }

        public static function validateDateTime($format, $value)
        {
            $d = \DateTime::createFromFormat($format, $value);
            return ($d && $d->format($format) === $value);
        }

        public static function hasDateValidate($field)
        {
            return (self::hasValidateType($field) &&
                (self::getValidateType($field) === self::VALIDATE_TYPE_DATE() || self::getValidateType($field) === self::VALIDATE_TYPE_DATETIME()));
        }

        public static function hasTimeValidate($field)
        {
            return (self::hasValidateType($field) &&
            (self::getValidateType($field) === self::VALIDATE_TYPE_TIME() || self::getValidateType($field) === self::VALIDATE_TYPE_HOUR_MINUTE()));
        }

        public static function hasDateOrDatetimeValidate($field)
        {
            return self::hasDateValidate($field) || self::hasTimeValidate($field);
        }

        public static function validateJSON($value = null)
        {
            if (! empty($value)) {
                @json_decode($value);
                return (json_last_error() === JSON_ERROR_NONE);
            }
            return false;
        }

        public static function validateType($type, $value)
        {
            if ($type === self::VALIDATE_TYPE_EMAIL() && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_URL() && ! filter_var($value, FILTER_VALIDATE_URL)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_DOMAIN() && ! preg_match("/^([a-zA-Z0-9]([-a-zA-Z0-9]{0,61}[a-zA-Z0-9])?\.)?([a-zA-Z0-9]{1,2}([-a-zA-Z0-9]{0,252}[a-zA-Z0-9])?)\.([a-zA-Z]{2,63})$/", $value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_STRING() && ! is_string($value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_INT() && ! is_int($value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_NUMBER() && ! is_numeric($value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_DATE() && ! self::validateDateTime("Y-m-d", $value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_DATETIME() && ! self::validateDateTime("Y-m-d H:i:s", $value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_TIME() && ! self::validateDateTime("H:i:s", $value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_HOUR_MINUTE() && ! self::validateDateTime("H:i", $value)) {
                return false;
            } elseif ($type === self::VALIDATE_TYPE_JSON() & ! self::validateJSON($value)) {
                return false;
            }
            return true;
        }

        public static function validateField($field, $value)
        {
            $response = [];
            if (self::hasValidateType(($field))) {
                $type = self::getValidateType($field);
                $validateType = self::validateType($type, $value);
                if (! $validateType) {
                    $response = self::setError($response, $field, self::VALIDATE_ERROR_TYPE(), [
                      "type" => $type
                  ]);
                }
            }

            if (self::hasValidateLength($field)) {
                $lengthError = [];
                if (self::hasValidateMinLength($field)) {
                    $minLength = self::getValidateMinLength($field);
                    if ($minLength !== false && strlen($value) < $minLength) {
                        $lengthError["min_length"] = $minLength;
                    }
                }
                if (self::hasValidateMaxLength($field)) {
                    $maxLength = self::getValidateMaxLength($field);
                    if ($maxLength !== false && strlen($value) > $maxLength) {
                        $lengthError["max_length"] = $maxLength;
                    }
                }
                if (! empty($lengthError)) {
                    $response = self::setError($response, $field, self::VALIDATE_ERROR_LENGTH(), $lengthError);
                }
            }

            $dateError = [];
            if (self::hasDateOrDatetimeValidate($field)) {
                if (self::hasDateValidate($field)) {
                    $format = "Y-m-d";
                } elseif (self::hasTimeValidate($field)) {
                    $format = "H:i:s";
                }

                if (self::hasValidateDateMin($field)) {
                    $min_date = self::getValidateDateMin($field);
                    if (new \DateTime($min_date) > new \DateTime($value)) {
                        $dateError["min_date"] = $min_date;
                    }
                }
                if (self::hasValidateDateMax($field)) {
                    $max_date = self::getValidateDateMax($field);
                    if (new \DateTime($max_date) < new \DateTime($value)) {
                        $dateError["max_date"] = $max_date;
                    }
                }

                if (! empty($dateError)) {
                    $response = self::setError($response, $field, self::VALIDATE_ERROR_DATE(), $dateError);
                }
            }
            return $response;
        }

        public static function validateFields($values = [])
        {
            $response = [];
            foreach ($values as $field => $value) {
                if (self::hasValidate($field)) {
                    $_response = self::validateField($field, $value);
                    if (count($_response)) {
                        $response = array_merge($response, $_response);
                    }
                }
            }
            return $response;
        }

        public static function validate($values = [])
        {
            $response = [];

            $mandatoryFields = self::getMandatoryFields();
            foreach ($mandatoryFields as $mandatoryField) {
                if (! in_array($mandatoryField, array_keys($values))) {
                    $response = self::setError($response, $mandatoryField, self::VALIDATE_ERROR_MISSING_FIELD());
                }
            }

            $_response = self::validateFields($values);
            if (count($_response)) {
                $response = array_merge($response, $_response);
            }

            return $response;
        }
    }
