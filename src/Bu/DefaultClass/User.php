<?php

namespace Bu\DefaultClass;

use Bu\Base;

class User extends \Bu\Base
{
    public static function DEF()
    {
        return [
            "table" => "user",
            "fields" => [
                "user_id" => [
                    "type" => self::TYPE_INT(),
                    "attr" => [ self::ATTR_AUTO_INCREMENT() ]
                ],
                "account_id" => [
                    "type" => self::TYPE_INT(),
                    "fk" => [
                        "class" => get_called_class()::GET_DEFAULT_FK_CLASS_ACCOUNT_ID()
                    ]
                ],
                "email" => [
                    "type" => self::TYPE_STRING()
                ],
                "name" => [
                    "type" => self::TYPE_STRING()
                ],
                "lastname" => [
                    "type" => self::TYPE_STRING(),
                    "attr" => [ self::ATTR_OPTIONAL() ]
                ],
                "password" => [
                    "type" => self::TYPE_STRING(),
										"attr" => [ self::ATTR_NOT_VISIBLE() ]
                ]
            ],
            "pk" => [ "user_id" ],
            "attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
        ];
    }

    public static function GET_DEFAULT_FK_CLASS_ACCOUNT_ID()
    {
        return "Bu\DefaultClass\Account";
    }
    public static function GET_DEFAULT_FK_CLASS_SESSION()
    {
        return "Bu\DefaultClass\Session";
    }

    public static function add($values = null)
    {
        $values["password"] = self::encrypt($values["password"]);
        return parent::add($values);
    }

    public static function validateCredentials($email = "", $password = "")
    {
        return self::findFirst("email = ? and password = ?", [
            "email" => $email,
            "password" => self::encrypt($password)
        ]);
    }

    public static function getNewSession($email, $password)
    {
        if ($user = self::validateCredentials($email, $password)) {
            $session = $user->associate(get_called_class()::GET_DEFAULT_FK_CLASS_SESSION());
            return $session;
        }
    }

    public static function getAPIDefaultValues($user)
    {
        return [
          "account_id" => $user->getValues("account_id")
        ];
    }
}
