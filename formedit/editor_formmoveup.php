<?php
$containerid=$_REQUEST['containerid'];
if($containerid=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
$hsconfig=getHsConfig();

$new = array();
$remember=null;
$_SESSION['editor'] = array_reverse($_SESSION['editor'],true);
foreach($_SESSION['editor'] as $key => $data)
{
    //echo $key."==".$containerid."<br>";
    if($key==$containerid)
    {
        $remember=$data;
    }
    else
    {
        $new[$key]=$data;
        if($remember!==null)
        {
            //echo "done<br>";
            $new[$containerid]=$remember;
            $remember=null;
        }
    }
}
if($remember!==null)
{
    $new[$containerid]=$remember;
    $remember=null;
}
$_SESSION['editor']=$new;
$_SESSION['editor'] = array_reverse($_SESSION['editor'],true);
