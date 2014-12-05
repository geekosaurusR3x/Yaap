<?php
require_once('baseParser.php');

class jsonParser extends baseParser {

	/**
	 * this one decode the data form json string
	 * @param String $data the data to decode
	 * @return Array
	 */
	public function decode($data){
		$return = json_decode($data, TRUE );
		if($return == null){
			throw new Exception('Json data mal formated');
		}
		return $return;
	}

	/**
	 * this one encode for responding into json
	 * @param Array $data the data to encode
	 * @return String
	 */
	public function encode($data){

		return json_encode($data);
	}
}
?>
