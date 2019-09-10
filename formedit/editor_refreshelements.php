<?php

$containerid=$_REQUEST['containerid'];
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


$elements=array();

foreach($_SESSION["editor"][$containerid]["elements"] as $key2=>$value2)
{
    $e = unserialize($_SESSION['editor'][$containerid]['elements'][$key2]);
    if($e)
    {
        $t=$e->property['bezeichnung'];
        if($t=="")
        {
            $t=$e->property['name'];
        }
        if($t=="")
        {
            $t=$e->property['project'];
        }
        
        $elements[$e->id]=$e->editorname." - ".$t.' ('.$e->id.')';
    }
}
asort($elements);

$first=true;
foreach($elements as $id=>$text)
{
    if($first==true)
    {
        $first=false;
    }
    else
    {
        echo "||";
    }
    echo $id."::".$text;
}