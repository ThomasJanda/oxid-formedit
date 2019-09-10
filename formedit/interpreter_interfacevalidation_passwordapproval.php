<?php
ob_start();

require_once __DIR__ . "/core/hsinitinterpreter.php";

$hsconfig = getHsConfig();

$otab=null;
$aform=null;
$interpreterid=$_REQUEST['interpreterid'];
$formularid=$_REQUEST['formularid'];
$action = $hsconfig->getNavi();
$index1value=$_REQUEST['index1value'];
$interpreter_interfacevalidation_passwordapproval=$_REQUEST['interpreter_interfacevalidation_passwordapproval'];

// we assume it is already in session because nobody calls ajax as a first page load.
$aform = $hsconfig->getInterpreterValue("formulare")[$formularid];

$otab=unserialize($aform['property']);

$oelements=array();
$validarray=array();
$validaction=$action;
foreach($aform["elements"] as $key=>$value)
{
    $oe = unserialize($value);
    $oe->interpreterInit();
    $oe->setTab($otab);
    $oelements[]=$oe;
    $validarray[$key]=$oe->getInterpreterRequestValue();
}

$classname = $otab->property['passwordvalidation_class'];
$ov=new $classname();
$ret=$ov->passwordapproval($formularid, $validarray, $validaction, $otab->getTableName(), $otab->getColumnIndex(), $index1value, $interpreter_interfacevalidation_passwordapproval);
if($ret==true)
    echo "1";
   
$content = trim(ob_get_contents());
ob_end_clean();

echo $content;   
?>
