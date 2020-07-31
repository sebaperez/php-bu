<?php

use Bu\Test\BuTest;
use Bu\API;

namespace Bu\BuUnit;

class APITest extends \Bu\Test\BuTest
{

    public function test_create_new_instance_of_api() {
        $api = \Bu\API::call("test");
        $this->assertNotNull($api);
        $this->assertInstanceOf("\Bu\API", $api);
    }

}

?>