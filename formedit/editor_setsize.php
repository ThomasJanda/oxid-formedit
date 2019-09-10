<?php
$classname=$_REQUEST['classname'];
$id=$_REQUEST['id'];
$left=$_REQUEST['left'];
$top=$_REQUEST['top'];
$width=$_REQUEST['width'];
$height=$_REQUEST['height'];
$containerid=$_REQUEST['containerid'];
$zindex=$_REQUEST['zindex'];
if($classname=="" || $id=="")
    die("");

require_once(__DIR__ . "/core/hsinit.php");
$hsconfig=getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";


$e = unserialize($_SESSION['editor'][$containerid]['elements'][$id]);
$e->left=$left;
$e->top=$top;
$e->width=$width;
$e->height=$height;
$e->zindex=$zindex;
$_SESSION['editor'][$containerid]['elements'][$id] = serialize($e);
