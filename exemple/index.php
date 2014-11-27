<?php
##declaration
#return array wich will be print as json
$return = array();

$auth = false;
#php session
session_start();
##include
#load include the api class
require('apigenerator.php');

#Execute load and execute the api
$api = new ApiGenerator();
$api->getRequest();
$api->load();
$api->executeAndSend();

?>
