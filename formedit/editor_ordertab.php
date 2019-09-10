<?php
require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$order=$_REQUEST['order'];
$tmp=array();
for($x=0;$x<count($order);$x++)
{
    echo $order[$x];
    $tmp[$order[$x]]=$_SESSION['editor'][$order[$x]];
}
$_SESSION['editor']=$tmp;

?>