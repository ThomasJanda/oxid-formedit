<?php
$id=$_REQUEST["id"];
if($id=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$e = unserialize($_SESSION['editor'][$id]['property']);
/*
echo $id;
echo '<pre>';
print_r($_SESSION);
echo '<pre>';
*/
echo $e->getEditorProperty();

?>