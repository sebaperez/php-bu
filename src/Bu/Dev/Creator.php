<?php

	namespace Bu\Dev;

	use Bu\Bu;

	class Creator {

		public $namespace;
		public $definition;

		public static function init($namespace, $definition) {
			return new Creator($namespace, $definition);
		}

		public function __construct($namespace, $definition) {
			$this->namespace = $namespace;
			$this->definition = $definition;
		}

		public function getNamespace() {
			return $this->namespace;
		}

		public function getDefinition() {
			return $this->definition;
		}

		public function error($i, $line) {
			echo("Error processing line $i: $line\n");
			exit();
		}

		public function getArrayFromDefinition() {
			$array = [];
			$definition = $this->getDefinition();
			$data = explode("\n", $definition);
			$table = false;

			for ($i = 0; $i < count($data); $i++) {
				$line = trim($data[$i]);
				$firstChar = substr($line, 0, 1);

				if ($firstChar == "-" && ! $table) {
					$this->error($i, $line);
				} else if ($firstChar == "-") {
					$line = str_replace("- ", "", $line);
					$lineData = explode("\t", $line);

					$columnName = $lineData[0];
					$columnType = $lineData[1];

					$array[$tableName]["columns"][$columnName] = [ "type" => $columnType ];
					if (isset($lineData[2]) && $lineData[2] == "pk") {
						array_push($array[$tableName]["pk"], $columnName);
					}
				} else if (! $table) {
					$table = true;
					$tableName = $line;
					$array[$tableName] = [ "columns" => [], "pk" => [] ];
				} else if ($line) {
					$this->error($i, $line);
				} else {
					$table = false;
				}
			}

			return $array;
		}

	}

?>
