<?php

$id=$_REQUEST['id'];
$containerid=$_REQUEST['containerid'];
$classname=$_REQUEST['classname'];
if($id=="" || $containerid=="" || $classname=="")
    die("");


require_once __DIR__ . "/core/hsinit.php";
require_once __DIR__ . "/core/hsiniteditor.php";
$hsconfig=getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";

ini_set('display_errors', 0);

$html="";
echo editor_loadelement($containerid, $id);

?>
