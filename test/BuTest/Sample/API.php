<?php

namespace Bu\Test\Sample;

use Bu\Base;

class API extends \Bu\API
{
    public static function API_MAP()
    {
        return [ "sample" => "Bu\Test\Sample\SampleClass" ];
    }
}
