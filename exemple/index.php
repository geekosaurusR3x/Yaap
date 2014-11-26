<?php
##declaration
#return array wich will be print as json
$return = array();

/** @global int This is this is for sending js or file */
$return_json = true;

#php session
session_start();
##include
#load include the api class
require('apigenerator.php');

#Execute load and execute the api
$api = new ApiGenerator();
$api->getRequest();
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
