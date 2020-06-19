<?php

	namespace Bu;

	class Bu {

		static function arraysAreEqualsUnSorted($array1, $array2) {
			return count(array_diff($array1, $array2)) === 0;
		}

	}

?>
