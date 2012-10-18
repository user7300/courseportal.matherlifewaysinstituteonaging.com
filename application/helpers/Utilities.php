<?php defined('BASEPATH') or exit('No direct script access allowed');  

function array_flatten($array, $flattened = array()) {
	foreach($array as $key => $val) {
		if(is_array($val))
			$flattened = array_flatten($val, $flattened);
		else
			array_push($flattened, $key, $val); 
	}
	return $flattened;
}

class PregMatch {
	
	private $pattern;
	
	public function __construct($pattern) {
		$this->pattern = $pattern;
	}
	
	public function match($string) {
		return is_string($string) && preg_match($this->pattern, $string);
	}
	
}