<?php

$id=$_REQUEST['id'];
$containerid=$_REQUEST['containerid'];
$classname=$_REQUEST['classname'];
if($id=="" || $containerid=="" || $classname=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";

$e = $_SESSION['editor'][$containerid]['elements'][$id];
$e = unserialize($e);
echo $e->getEditorRender();
