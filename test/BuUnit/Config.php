<?php

    namespace Bu\BuUnit;

    class Config extends \Bu\Test\BuTest {

        public function BASE_CLASS() { return "\Bu\Test\Sample"; }
        public function CONFIG_CLASS() { return "\Bu\BuUnit\Config"; }

        public function DEFAULT() {
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
                    "function" => function() {
                        $object1 = $this->getNew("SampleClass");
                        $object2 = $this->getNew("SampleClass");
                        return [ "id1" => $object1->getValue("sampleclass_id"), "id2" => $object2->getValue("sampleclass_id") ];
                    },
                    "key" => [ "id1", "id2" ]
                ]
            ];
        }

    }

?>