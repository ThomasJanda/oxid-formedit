<?php
$containerid=$_REQUEST['id'];
if($containerid=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";

$hsconfig=getHsConfig();
$d=opendir($hsconfig->getBasePath."elements");
while ($file = readdir ($d))
{
    if(is_file($hsconfig->getBasePath."elements/".$file))
    {
        include_once($hsconfig->getBasePath."elements/".$file);
    }
}
closedir($d);

$override=$_REQUEST['override'];

foreach($_SESSION['editor'] as $containerid=>$aform)
{
    $otab=unserialize($aform['property']);
    foreach($aform['elements'] as $id=>$object)
    {
        $e = unserialize($object);
        if($e->getLanguageId()=="" || ($e->getLanguageId()!="" && $override=="1"))
        {
            $e->setTab($otab);
            $e->generateLanguageId(); 
            $_SESSION['editor'][$containerid]['elements'][$id]=serialize($e);        
        }
    }
}
?>