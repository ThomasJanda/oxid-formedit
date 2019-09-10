<?php

$classname = $_REQUEST['classname'];
$id = $_REQUEST['id'];
$containerid = $_REQUEST['containerid'];
if ($containerid == "" || $classname == "" || $id == "")
    die;

require_once __DIR__ . "/core/hsinit.php";
$hsconfig = getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";

$e = unserialize($_SESSION['editor'][$containerid]['elements'][$id]);
if($e)
    echo $e->getEditorProperty();
else
{
    echo "Cannot load properties.<br>";
    echo "Classname: ".$classname."<br>";
    echo "Container id: ".$containerid."<br>";
    echo "Id: ".$id."<br>";
}
