<?php

	namespace Bu;

	class BuStatic extends Bu {

		public static function get($directory) {
			return new BuStatic($directory);
		}

		public function __construct($directory) {
			$this->directory = $directory;
			$this->tables = [];
		}

		public function set($class, $table) {
			$this->tables[$class] = $table;
		}

		public function getDirectory() {
			return $this->directory;
		}

		public function getTables() {
			return $this->tables;
		}

		public function getTableData($table) {
			$content = file_get_contents($this->getDirectory() . "/" . $table);
			if ($content) {
				return json_decode($content, true);
			}
		}

		public function clearTable($table) {
			\Bu\BuDB::truncate($table);
		}

		public function run() {
			$tables = $this->getTables();

			foreach ($tables as $class => $table) {
				if ($class::isStatic()) {
					$this->clearTable($class::getTable());
					$data = $this->getTableData($table);
					foreach ($data as $row) {
						$class::add($row);
					}
				} else {
					throw new \Bu\Exception\StaticNotAllowed($table);
				}
			}
		}

	}

?>
