<?php
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
?>
