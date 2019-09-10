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

$langa=array();
$e = unserialize($_SESSION['editor'][$containerid]['property']);
//$lang=$e->getLanguageArray()."\n";
unset($e);

/*
foreach($_SESSION['editor'][$containerid]['elements'] as $id=>$object)
{
    $e = unserialize($object);
    $tmp=$e->getLanguageArray();
    foreach($tmp as $name=>$value)
    {
        $langa[$name]=$value;
    }
}
*/
foreach($_SESSION['editor'] as $containerid => $aform)
{
    foreach($aform['elements'] as $id=>$object)
    {
        $e = unserialize($object);
        $tmp=$e->getLanguageArray();
        if(is_array($tmp))
        {
            foreach($tmp as $name=>$value)
            {
                $langa[$name]=$value;
            }
        }
    }
}

$tmp="\n";
$tmp.='$lang'." = array(\n";
foreach($langa as $lid => $lang)
{
    $tmp.="'".$lid."' => array(\n";
    foreach($lang as $name => $value)
    {
        if(is_array($value))
        {
            $tmp.="\t'".$name."' => array(\n";
            foreach($value as $n=>$v)
            {
                $tmp.="\t\t'".$n."' => '".str_replace('"','\"',$v)."',\n"; 
            }
            $tmp.="\t),\n";
        }
        else
        {
            $tmp.="\t'".$name."' => '".str_replace('"','\"',$value)."',\n";       
        }
    }
    $tmp.="),\n";
}
$tmp.=");";

echo $tmp;
?>