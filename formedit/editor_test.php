<?php
require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$s=urlencode(serialize($_SESSION["editor"]));
echo $s;
