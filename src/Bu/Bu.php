<?php

	namespace Bu;

	class Bu {

		static function arraysAreEqualsUnSorted($array1, $array2) {
			return (count(array_diff($array1, $array2)) === 0) && (count(array_diff($array2, $array1)) === 0);
		}

		static function getDiffArrayKeys($array1, $array2) {
			return array_diff($array1, $array2);
		}

	}

?>
