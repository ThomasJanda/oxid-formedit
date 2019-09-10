<?php
$id=$_REQUEST['id'];
$containerid=$_REQUEST['containerid'];
$newcontainerid=$_REQUEST['newcontainerid'];
$classname=$_REQUEST['classname'];
if($id=="" || $containerid=="" || $classname=="" || $newcontainerid=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";

$e = $_SESSION['editor'][$containerid]['elements'][$id];
$e = unserialize($e);
$e->createid();
$e->containerid=$newcontainerid;
echo $e->getEditorRender();

$_SESSION['editor'][$newcontainerid]['elements'][$e->id]=serialize($e);
?>
