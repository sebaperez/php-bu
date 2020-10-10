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
}
