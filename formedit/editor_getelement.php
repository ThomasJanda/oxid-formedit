<?php
$classname=$_REQUEST['classname'];
$left=$_REQUEST['left'];
$top=$_REQUEST['top'];
$containerid=$_REQUEST['containerid'];
if($classname=="" || $containerid=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";

$e=new $classname();
$e->left=$left;
$e->top=$top;
$e->containerid=$containerid;
//echo "hallo";
//echo $e->getEditorBeforeRender();
echo $e->getEditorRender();
//echo $e->getEditorAfterRender();

$_SESSION['editor'][$containerid]['elements'][$e->id]=serialize($e);
?>
