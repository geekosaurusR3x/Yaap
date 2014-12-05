<?php
require_once('baseParser.php');

class jsonParser extends baseParser {

	/**
	 * this one decode the data from the body of the request
	 */
	public function decode($data){
		$return = json_decode($data, TRUE );
		if($return == null){
			throw new Exception('Json data mal formated');
		}
		return $return;
	}

	/**
	 * this one encode for responding
	 */
	public function encode($data){

		return json_encode($data);
	}
}
?>
