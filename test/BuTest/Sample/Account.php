<?php

namespace Bu\Test\Sample;

use Bu\DefaultClass;

class Account extends \Bu\DefaultClass\Account
{

    public static function GET_DEFAULT_FK_CLASS_USER_ID() { return "Bu\Test\Sample\User"; }

}

?>