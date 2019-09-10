<?php
$containerid=$_REQUEST['containerid'];
$source=trim($_REQUEST['source']);
if($containerid=="" || $source=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

//alle klassen includieren, das alles klappt

$d=opendir($hsconfig->getBasePath."elements");
$files=array();
while ($file = readdir ($d))
{
    if(is_file($hsconfig->getBasePath."elements/".$file) && substr(strtolower($file),strlen($file)-4)==".php")
    {
        $files[]=$file;
    }
}
closedir($d);
sort($files);
for($x=0;$x<count($files);$x++)
{
    $file=$files[$x];
    include_once($hsconfig->getBasePath."elements/".$file);
}




$e = unserialize(base64_decode($source));
$e->createid();
$e->containerid=$containerid;
echo $e->getEditorRender();

$_SESSION['editor'][$containerid]['elements'][$e->id]=serialize($e);

?>