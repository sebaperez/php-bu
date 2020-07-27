<?php

use Bu\Test\Sample\Account;
use Bu\Test\Sample\User;
use Bu\Test\Sample\Session;
use Bu\Test\BuTest;

namespace Bu\BuUnit;

class DefaultTest extends \Bu\Test\BuTest
{
	
    public static function CONFIG_CLASS() { return "\Bu\BuUnit\Config"; }
    
    public function test_create_new_account() {
        $account = $this->getNew("Account");
        $this->assertNotNull($account);
        $this->assertInstanceOf("\Bu\Test\Sample\Account", $account);
        $this->assertIsInt($account->getValue("account_id"));
    }

    public function test_add_new_user_to_account() {
        $account = $this->getNew("Account");
        $user = $account->addUser([
            "email" => $this->getRandomString(),
            "name" => $this->getRandomString(),
            "password" => $this->getRandomString()
        ]);
        $this->assertNotNull($user);
        $this->assertEquals($account->getValue("account_id"), $user->getValue("account_id"));
        $this->assertInstanceOf("\Bu\Test\Sample\User", $user);
    }

}

?>