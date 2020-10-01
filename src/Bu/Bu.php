<?php

    namespace Bu;

    class Bu
    {
        public static function arraysAreEqualsUnSorted($array1, $array2)
        {
            return (count(array_diff($array1, $array2)) === 0) && (count(array_diff($array2, $array1)) === 0);
        }

        public static function getDiffArrayKeys($array1, $array2)
        {
            return array_diff($array1, $array2);
        }

        public static function encrypt($string)
        {
            return md5($string);
        }

        public static function getRandomString($length = 64)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }
    }
