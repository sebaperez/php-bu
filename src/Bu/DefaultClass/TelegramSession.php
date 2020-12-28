<?php

namespace Bu\DefaultClass;

use Bu\Base;

class TelegramSession extends \Bu\Base
{
    public static function DEF()
    {
        return [
            "table" => "telegram_session",
            "fields" => [
                "session_id" => [
                    "type" => self::TYPE_INT(),
										"fk" => [
											"class" => get_called_class()::GET_DEFAULT_FK_CLASS_SESSION_ID()
										]
                ],
                "telegram_user_id" => [
                    "type" => self::TYPE_INT()
                ],
            ],
            "pk" => [ "session_id", "telegram_user_id" ],
            "attr" => [ self::ATTR_WITH_START_DATE(), self::ATTR_WITH_END_DATE() ]
        ];
    }

		public static function GET_DEFAULT_FK_CLASS_SESSION_ID()
		{
				return "Bu\DefaultClass\Session";
		}

		public static function findByUserId($user_id) {
			return self::findFirst("telegram_user_id = ?", [ "telegram_user_id" => $user_id ]);
		}

		public function getSession() {
			return $this->getObject("session_id");
		}

}
