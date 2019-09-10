<?php
$id=$_REQUEST['containerid'];
if($id=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

unset($_SESSION['editor'][$id]);
?>