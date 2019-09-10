<?php
$containerid=$_REQUEST['id'];
if($containerid=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";

$hsconfig=getHsConfig();
$d=opendir($hsconfig->getBasePath."elements");
while ($file = readdir ($d))
{
    if(is_file($hsconfig->getBasePath."elements/".$file) && substr(strtolower($file),strlen($file)-4)==".php")
    {
        include_once($hsconfig->getBasePath."elements/".$file);
    }
}
closedir($d);


$e = unserialize($_SESSION['editor'][$containerid]['property']);
$sqlstring="\n".$e->getSQL()."\n";
$table=$e->property['table'];
unset($e);

foreach($_SESSION['editor'][$containerid]['elements'] as $id=>$object)
{
    $e = unserialize($object);
    $tmp=$e->getSQL($table);
    if($tmp!="")
        $sqlstring.=$tmp."\n";
    unset($e);
}

echo $sqlstring;
?>