<?php
$id=$_REQUEST['containerid'];
if($id=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$e = unserialize($_SESSION['editor'][$id]['property']);
$e->setEditorProperty();
$_SESSION['editor'][$id]['property']=serialize($e);
