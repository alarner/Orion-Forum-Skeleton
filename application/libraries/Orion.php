<?php
class Orion {
	public function ezdie() {
		$argList = func_get_args();
		$this->ezhelper($argList);
		exit;
	}
	
	public function ezecho() {
		$argList = func_get_args();
		$this->ezhelper($argList);
	}
	
	public function def($key, $array, $default = null) {
		return array_key_exists($key, $array) ? $array[$key] : null;
	}
	
	private function ezhelper($argList) {
		echo '<pre>';
		foreach($argList as $arg) {
			echo htmlentities(print_r($arg, true));
			echo "\n";
		}
		echo '</pre>';
	}
}
