<?php

namespace Bu\Test\Sample;

use Bu\DefaultClass;

class Session extends \Bu\DefaultClass\Session
{

    public static function GET_DEFAULT_FK_CLASS_USER_ID() { return "Bu\Test\Sample\User"; }

}

?>