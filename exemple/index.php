<?php
##declaration
#return array wich will be print as json
$return = array();
#allocate url array;
$elementsUrl;
#usr authentification true/false
$auth = false;
#group of the usr (permit to limit the access of the api)
$group = "user";
#reson of error
$resson = null;

/** @global int This is this is for sending js or file */
$return_json = true;

#php session
session_start();
##include
#load include the api class
require('apigenerator.php');

##exploding url for geting info
//$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$url = $_SERVER['REQUEST_URI'];
$elementsUrl = explode("/",$url);
##remove 2 frist case because of nulity
array_splice($elementsUrl,0,2);

for($i=0; $i < count($elementsUrl); $i++)
{
	$elementsUrl[$i] = urldecode($elementsUrl[$i]);
}

#Execute load and execute the api
$api = new ApiGenerator();
$api->setCache(false);
$api->load();
$return = $api->execute($elementsUrl,$auth,$group);

##injecting auth status
$return['auth'] = $auth;
if(!$auth && isset($resson_auth))
{
    $return['error_login'] = $resson_auth;
}

#########################################################################################################################
##Result
if($return_json)
{
    ##declare json responce
    header('Content-Type: application/json');
    ##printing result
    echo json_encode($return);
}

?>
