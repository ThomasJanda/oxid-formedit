<?php
require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$e = new basetab();
$_SESSION['editor'][$e->getTabId()]['property']=serialize($e);
$_SESSION['editor'][$e->getTabId()]['elements']=array();
echo $e->getTabId();

?>