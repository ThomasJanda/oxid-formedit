<?php
require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$classname=$_REQUEST['classname'];
$id=$_REQUEST['id'];
$tabid=$_REQUEST['tabid'];
$containerid=$_REQUEST['containerid'];

if($containerid=="" || $classname=="" || $id=="")
    die("");

if($tabid=="")
    $tabid=0;

$_SESSION[$containerid][$classname][$id] = $tabid;
