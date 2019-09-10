<?php
$id=$_REQUEST['id'];
$containerid=$_REQUEST['containerid'];
if($containerid=="" || $id=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

//for tab element, delete all hasparentcontrol, if tab is deleted
$d=opendir($hsconfig->getBasePath."elements");
while ($file = readdir ($d))
{
    if(is_file($hsconfig->getBasePath."elements/".$file) && substr(strtolower($file),strlen($file)-4)==".php")
    {
        include_once($hsconfig->getBasePath."elements/".$file);
    }
}
closedir($d);
$e = unserialize($_SESSION['editor'][$containerid]['elements'][$id]);

$tabidelement=$containerid."_".$id."_";

foreach($_SESSION["editor"] as $key=>$value)
{
    foreach($_SESSION["editor"][$key]["elements"] as $key2=>$value2)
    {
        $e = unserialize($_SESSION['editor'][$key]['elements'][$key2]);
        if($e)
        {
            if($e->getParentControl()!="")
            {      
                if(substr($e->getParentControl(),0,strlen($tabidelement))==$tabidelement)
                {
                    $e->setParentControl("");
                    $_SESSION["editor"][$key]["elements"][$key2]=serialize($e);
                    
                    echo "
                    var obj = $('#".$key2."');
                    refreshElement(obj); 
                    ";
                }
            }
        }
    }
}






unset($_SESSION['editor'][$containerid]['elements'][$id]);

?>