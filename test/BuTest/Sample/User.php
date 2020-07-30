<?php

namespace Bu\Test\Sample;

use Bu\DefaultClass;

class User extends \Bu\DefaultClass\User
{

    public static function GET_DEFAULT_FK_CLASS_ACCOUNT_ID() { return "Bu\Test\Sample\Account"; }
    public static function GET_DEFAULT_FK_CLASS_SESSION() { return "Bu\Test\Sample\Session"; }

}

?>