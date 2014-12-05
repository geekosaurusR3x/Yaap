<?php
require_once('baseParser.php');
/**
 * Extend jsonParser Class
 * The parser is for jsonp data type
 */
class javascriptParser extends jsonParser {

	/**
	 * this one decode the data form json string
	 * @param String $data the data to decode
	 * @return Array
	 */
	public function decode($data){
		return parent::decode($data);
	}

	/**
	 * this one encode for responding into json
	 * @param Array $data the data to encode
	 * @return String
	 */
	public function encode($data){
		return $_GET['callback']."(".parent::encode($data).")";
	}
}
?>
