<?php
/**
 * Abstract class do noting alone
 * This one represent the data parser and formater for a content type
 */
abstract class BaseParser{

	/**
	 * this one decode the data from the body of the request
	 */
	abstract public function decode($data);

	/**
	 * this one encode for responding
	 */
	abstract public function encode($data);
}
?>
