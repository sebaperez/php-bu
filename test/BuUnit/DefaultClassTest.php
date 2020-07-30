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

    public function test_account_has_fk_reference_from_user() {
        $fks = \Bu\Test\Sample\Account::getExternalClassFK("\Bu\Test\Sample\User");
        $this->assertCount(1, $fks);
        $this->assertEquals("account_id", $fks[0]);
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

    public function test_login_user() {
        $password = $this->getRandomString();
        $user = $this->getNew("User", [ "password" => $password ]);
        $this->assertNotNull(\Bu\Test\Sample\User::validateCredentials($user->getValue("email"), $password));
        $session = \Bu\Test\Sample\User::getNewSession($user->getValue("email"), $password);
        $this->assertNotNull($session);
        $_user = $session->getUser();
        $this->assertEquals($user->getValue("user_id"), $_user->getValue("user_id"));
    }

}

?>