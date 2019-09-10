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

// we assume it is already in session because nobody calls ajax as a first page load.
$aform = $hsconfig->getInterpreterValue("formulare")[$formularid];

$otab=unserialize($aform['property']);

$oelements=array();
$validarray=array();
$validaction=$action;

//scriptphp
foreach($aform["elements"] as $key=>$value) {
    /**
     * @var basecontrol $oe
     */
    $oe = unserialize($value);
    $oelements[$key] = $oe;
}
foreach ($oelements as $key => $oe)
{
    $oe->interpreterInit();
    $oe->setTab($otab);
    $validarray[$key]=$oe->getInterpreterRequestValue();
}


$classname = $otab->property['passwordvalidation_class'];
$ov=new $classname();
$ov->init($formularid, $validarray, $validaction, $otab->getTableName(), $otab->getColumnIndex(), $index1value);

$content = trim(ob_get_contents());
ob_end_clean();

echo $content;
