<?php

namespace Bu\BuUnit;

use Bu\Test\Sample\SampleClass;
use Bu\Test\Sample\SampleClassMultiplePK;
use Bu\Test\BuTest;
use Bu\BuException;
use Bu\Base;

class ValidateTest extends \Bu\Test\BuTest
{
    public function test_validate_missing_fields()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([]);
        $this->assertNotEmpty($validation);
        $this->assertCount(1, $validation);
        $this->assertArrayHasKey("name", $validation);
        $this->assertArrayHasKey("error", $validation["name"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_MISSING_FIELD(), $validation["name"]["error"]);
    }

    public function test_validate_type()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "optional" => $this->getRandomString()
        ]);
        $this->assertCount(1, $validation);
        $this->assertArrayHasKey("optional", $validation);
        $this->assertArrayHasKey("error", $validation["optional"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_TYPE(), $validation["optional"]["error"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_TYPE_EMAIL(), $validation["optional"]["details"]["type"]);
    }

    public function test_validate_min_length()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "optional" => $this->getRandomString(1) . "@t.com"
        ]);
        $this->assertArrayHasKey("optional", $validation);
        $this->assertArrayHasKey("error", $validation["optional"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_LENGTH(), $validation["optional"]["error"]);
        $this->assertEquals(10, $validation["optional"]["details"]["min_length"]);
    }

    public function test_validate_max_length()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "optional" => $this->getRandomString(15) . "@t.com"
        ]);
        $this->assertArrayHasKey("optional", $validation);
        $this->assertArrayHasKey("error", $validation["optional"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_LENGTH(), $validation["optional"]["error"]);
        $this->assertEquals(20, $validation["optional"]["details"]["max_length"]);
    }

    public function test_validate_min_date()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "date" => "2019-01-01"
        ]);
        $this->assertArrayHasKey("date", $validation);
        $this->assertArrayHasKey("error", $validation["date"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["date"]["error"]);
        $this->assertEquals("2020-01-01", $validation["date"]["details"]["min_date"]);
    }

    public function test_validate_max_date()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "date" => "2021-01-01"
        ]);
        $this->assertArrayHasKey("date", $validation);
        $this->assertArrayHasKey("error", $validation["date"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["date"]["error"]);
        $this->assertEquals("2020-01-31", $validation["date"]["details"]["max_date"]);
    }

    public function test_validate_min_time()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "time" => "07:00:00"
        ]);
        $this->assertArrayHasKey("time", $validation);
        $this->assertArrayHasKey("error", $validation["time"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["time"]["error"]);
        $this->assertEquals("14:00:00", $validation["time"]["details"]["min_date"]);
    }

    public function test_validate_max_time()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "time" => date('H:i:s', strtotime(date('H:i:s') . ' + 1 minute'))
        ]);
        $this->assertArrayHasKey("time", $validation);
        $this->assertArrayHasKey("error", $validation["time"]);
        $this->assertEquals(\Bu\Test\Sample\SampleClass::VALIDATE_ERROR_DATE(), $validation["time"]["error"]);
        $this->assertEquals("now", $validation["time"]["details"]["max_date"]);
    }

    public function test_validate_max_time_pass_with_now()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "max_time" => date('H:i:s', strtotime(date('H:i:s') . ' - 1 minute'))
        ]);
        $this->assertEmpty($validation);
        $this->assertArrayNotHasKey("max_time", $validation);
    }

    public function test_validate_ok()
    {
        $validation = \Bu\Test\Sample\SampleClass::validate([
            "name" => $this->getRandomString(),
            "optional" => $this->getRandomString(10) . "@t.com"
        ]);
        $this->assertEmpty($validation);
    }

    public function assertValidType($type, $value)
    {
        return $this->assertTrue(\Bu\Base::validateType($type, $value), "Failed asserting that $value is a valid $type");
    }

    public function assertNotValidType($type, $value)
    {
        return $this->assertFalse(\Bu\Base::validateType($type, $value), "Failed asserting that $value is not a valid $type");
    }

    public function assertValid($type, $values)
    {
        foreach ($values["valid"] as $value) {
            $this->assertValidType($type, $value);
        }
        foreach ($values["invalid"] as $value) {
            $this->assertNotValidType($type, $value);
        }
    }

    public function test_valid_email()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_EMAIL(), [
            "valid" => [
                "test@test.com",
                "test@test.edu.ar",
                "test@rare.domain"
            ],
            "invalid" => [
                "@test.com",
                "test.com",
                $this->getRandomString()
            ]
        ]);
    }

    public function test_valid_url()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_URL(), [
            "valid" => [
                "http://test.com",
                "https://test.com",
                "http://test.com/test",
                "http://www.test.com/test",
                "http://www.test.com/test.html",
                "http://www.test.com/test.html?p=1",
                "http://www.test.com/test.html?p=1&t=2",
                "ftp://test.com"
            ],
            "invalid" => [
                "test.com",
                "test.com/url",
                $this->getRandomString(),
                "1",
                1
            ]
        ]);
    }

    public function test_valid_domain()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_DOMAIN(), [
            "valid" => [
                "test.com",
                "rare.domain"
            ],
            "invalid" => [
                "http://test.com",
                $this->getRandomString(),
                "1",
                1
            ]
        ]);
    }

    public function test_valid_string()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_STRING(), [
            "valid" => [
                $this->getRandomString(),
                "test.com",
                "1"
            ],
            "invalid" => [
                1
            ]
        ]);
    }

    public function test_valid_int()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_INT(), [
            "valid" => [
                1
            ],
            "invalid" => [
                1.0,
                1.2,
                $this->getRandomString(),
                "1"
            ]
        ]);
    }

    public function test_valid_number()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_NUMBER(), [
            "valid" => [
                1,
                1.0,
                1.2,
                "1",
                "1.2"
            ],
            "invalid" => [
                $this->getRandomString()
            ]
        ]);
    }

    public function test_valid_date()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_DATE(), [
            "valid" => [
                "2020-01-01"
            ],
            "invalid" => [
                $this->getRandomString(),
                "2020-01-01 14:14:14",
                "2020-13-01",
                "2020-02-30"
            ]
        ]);
    }

    public function test_valid_datetime()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_DATETIME(), [
            "valid" => [
                "2020-01-01 14:14:14",
            ],
            "invalid" => [
                $this->getRandomString(),
                "2020-01-01",
                "2020-13-01",
                "2020-02-30",
                "14:14:14",
                "2020-01-01 24:14:14",
                "2020-01-01 14:14:60",
                "2020-01-01 14:60:14"
            ]
        ]);
    }

    public function test_valid_time()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_TIME(), [
            "valid" => [
                "14:14:14"
            ],
            "invalid" => [
                $this->getRandomString(),
                "2020-01-01",
                "2020-13-01",
                "2020-02-30",
                "2020-01-01 14:14:14",
                "24:14:14",
                "14:14:60",
                "14:60:14"
            ]
        ]);
    }

    public function test_valid_time_hour_minute()
    {
        $this->assertValid(\Bu\Base::VALIDATE_TYPE_HOUR_MINUTE(), [
            "valid" => [
                "14:14"
            ],
            "invalid" => [
                $this->getRandomString(),
                "2020-01-01",
                "2020-13-01",
                "2020-02-30",
                "2020-01-01 14:14:14",
                "14:14:14",
                "24:14",
                "14:60"
            ]
        ]);
    }
}
