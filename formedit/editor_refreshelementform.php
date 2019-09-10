<?php
$id=$_REQUEST["containerid"];
if($id=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$e = unserialize($_SESSION['editor'][$id]['property']);
$name=$e->getTabName();
if(trim($name)=="")
    $name="Formular".$e->getTabId();
else
    $name.=" (".$e->getTabId().")";
echo $name;
?>