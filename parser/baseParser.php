<?php
/**
 * Abstract class do noting alone
 * This one represent the data parser and formater for a content type
 */
abstract class BaseParser{

	/**
	 * this one decode the data from the body of the request
	 * must throw a error with a message for explaning in case of impossibilty of decoding
	 * @param String $data the data to decode
	 * @return Array
	 */
	abstract public function decode($data);

	/**
	 * this one encode for responding
	 * @param Array  $data the data to encode
	 * @return String
	 */
	abstract public function encode($data);
}
?>
