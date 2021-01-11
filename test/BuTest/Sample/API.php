<?php

namespace Bu\Test\Sample;

use Bu\Base;

class API extends \Bu\API
{
    public static function SESSION_CLASS()
    {
        return "Bu\Test\Sample\Session";
    }
    public static function USER_CLASS()
    {
        return "Bu\Test\Sample\User";
    }
    public static function ACCOUNT_CLASS()
    {
        return "Bu\Test\Sample\Account";
    }

    public function getMethods() {
      return array_merge(parent::getMethods(), [
				"class/command" => [
					"function" => function($params) {

					}
				],
				"telegram/test" => [
					"attr" => [ self::API_ATTRIBUTE_NO_REQUIRES_LOGIN() ],
					"function" => function($params) {
						return $this->setOK("ok");
					}
				]
			]);
    }
}
