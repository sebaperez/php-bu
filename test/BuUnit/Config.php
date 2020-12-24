<?php

    namespace Bu\BuUnit;

    class Config extends \Bu\Test\BuTest
    {
        public function BASE_CLASS()
        {
            return "\Bu\Test\Sample";
        }
        public function CONFIG_CLASS()
        {
            return "\Bu\BuUnit\Config";
        }

        public function DEFAULT()
        {
            return [
                "\Bu\Test\Sample\SampleClass" => [
                    "values" => [
                        "name" => $this->getRandomString()
                    ],
                    "key" => [ "sampleclass_id" ]
                ],
                "\Bu\Test\Sample\SampleClassMultiplePK" => [
                    "values" => [
                        "name" => $this->getRandomString()
                    ],
                    "function" => function () {
                        $object1 = $this->getNew("SampleClass");
                        $object2 = $this->getNew("SampleClass");
                        return [ "id1" => $object1->getValue("sampleclass_id"), "id2" => $object2->getValue("sampleclass_id") ];
                    },
                    "key" => [ "id1", "id2" ]
                ],
                "\Bu\Test\Sample\Account" => [
                    "values" => [
                        "name" => $this->getRandomString()
                    ],
                    "key" => [ "account_id" ]
                ],
                "\Bu\Test\Sample\User" => [
                    "values" => [
                        "email" => $this->getRandomEmail(),
                        "name" => $this->getRandomString(),
                        "password" => $this->getRandomString()
                    ],
                    "key" => [ "user_id" ],
                    "function" => function () {
                        $account = $this->getNew("Account");
                        return [ "account_id" => $account->getValue("account_id") ];
                    }
                ],
                "\Bu\Test\Sample\Session" => [
                    "key" => [ "session_id" ],
                    "function" => function () {
                        $user = $this->getNew("User");
                        return [ "user_id" => $user->getValue("user_id") ];
                    }
                ],
                "\Bu\Test\Sample\SessionChild" => [
                  "key" => [ "sessionchild_id" ],
                  "function" => function () {
                      $session = $this->getNew("Session");
                      return [ "session_id" => $session->getValue("session_id") ];
                  }
                ]
            ];
        }
    }
