<?php

$containerid=$_REQUEST['containerid'];
if($containerid=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$ret = "";
foreach($_SESSION['editor'] as $id => $data)
{
    $e = unserialize($data['property']);
    $name=$e->getTabName();
    if(trim($name)=="")
        $name="Formular".$e->getTabId();
    else
        $name.=" (".$e->getTabId().")";

    if($ret!="")
        $ret.="||";
    $ret.=$id."::".$name;

}
echo $ret;
