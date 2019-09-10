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


//alle unterschiedlichen tops rausziehen
$tmptops=array();
$tops=array();
foreach($_SESSION['editor'][$containerid]['elements'] as $id=>$object)
{
    $e = unserialize($object);
    $tmptops[$e->top]="1";
}
//sortieren nach dem schlüssel
ksort($tmptops);
foreach($tmptops as $key=>$top)
{
    $tops[]=$key;
}

//alle unterschiedlichen lefts rausziehen
$tmplefts=array();
$lefts=array();
foreach($_SESSION['editor'][$containerid]['elements'] as $id=>$object)
{
    $e = unserialize($object);
    $tmplefts[$e->left]="1";
}
//sortieren nach dem schlüssel
ksort($tmplefts);
foreach($tmplefts as $key=>$left)
{
    $lefts[]=$key;
}

//jetzt von oben nach unten, von links nach rechts
$zindex=1000;
foreach($tops as $top)
{
    foreach($lefts as $left)
    {
        foreach($_SESSION['editor'][$containerid]['elements'] as $id=>$object)
        {
            $e = unserialize($object);
            if($e->left==$left && $e->top==$top)
            {
                //echo $zindex."<br>";
                $e->property['taborder']=$zindex;
                $_SESSION['editor'][$containerid]['elements'][$id]=serialize($e);
                $zindex+=100;
            }
        }
    }
}

?>