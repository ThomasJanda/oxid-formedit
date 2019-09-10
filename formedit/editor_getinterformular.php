<?php
require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();



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


foreach($_SESSION['editor'] as $containerid=>$tab)
{
    $otab=unserialize($tab['property']);
    $containerid_tab=$otab->getCustomerId();
    
    $b=trim($otab->getTabName());
    if($b!="")
    {
        $b.=" (".$otab->getTabId().")";
    }
    else
    {
        $b=$otab->getTabId();
    }
    $b="\n\r".$b."\n\r";
    $first=true;
    
    foreach($_SESSION['editor'][$containerid]['elements'] as $elementid=>$element)
    {
        $oelement=unserialize($element);
        if($oelement->name=="session_index")
        {
            if($first==true)
            {
                echo $b;
                $first=false;
            }
                
            $containerid_element=$oelement->getCustomerId();
            echo $oelement->property['bezeichnung']." => #SESSION.".$containerid_tab.".".$containerid_element."#\n\r";
        }
    }
}


?>
