<?php

namespace Bu\Test\Sample;

use Bu\DefaultClass;

class TelegramSession extends \Bu\DefaultClass\TelegramSession
{
    public static function GET_DEFAULT_FK_CLASS_SESSION()
    {
        return "Bu\Test\Sample\Session";
    }
}
