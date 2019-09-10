<?php

// fix problem where our php.exe deletes empty entries from the request array
// this problem should be gone when we update php or we move to google infrastructure.
foreach ($_REQUEST as &$r) {
    if ($r == "#CP_EMPTY_STRING_FIX#") $r = "";
}

require_once __DIR__ . "/core/hsinitinterpreter.php";

$hsConfig = getHsConfig();

$otab=null;
$aform=null;

// The "ini" array contains all keys from the request array, plus the mandatory values that we want to have at least set.
$ini = $hsConfig->getIni();

$index1value = $ini["index1value"];
$index2value = $ini["index2value"];
$kennzeichen1value = $ini["kennzeichen1value"];

$interpreterid = $ini['interpreterid'];
$formularid = $ini['formularid'];

if (!$ini["project"]) {
    // now it is mandatory to send the project name on each load
    echo "No project detected.";
    die;
}

$path = $ini["project"];

// check if it is a relative path, not a url or an absolute path (including windows)
if (preg_match('~^(?!http)(?!/)(?![CDEF]:)~', $path)) {
    // means it is a modules folder. make it absolute.
    $path = shopInterface::getInstance()->getModulesDir() . "/$path";
}

// setting absolute path of the current used project.
$hsConfig->setInterpreterValue("projectname", $path);

// Start form id
$startForm = $ini['startform'];
$hsConfig->setInterpreterValue("startform", $startForm);
// Start form name (customer id)
$startFormName = $ini["startformname"];
$hsConfig->setInterpreterValue("startformname", $startFormName);

/* the form can start with parameters, which will write into the session at the first start */
$hsConfig->setInterpreterValue("startparam", $ini['startparam']);

$parser = cpfFileParser::getInstance();
$projectForms = $parser->parseCpf($path);
$hsConfig->setInterpreterValue("formulare", $projectForms);

$aform = $projectForms[$formularid];
$otab = unserialize($aform['property']);

if (is_array($aform["elements"]) || is_object($aform["elements"])) {

    //for simple select within a module
    $tmpElements=null;
    foreach ($aform["elements"] as $eId => $eData) {
        $oe = unserialize($eData);
        $oe->interpreterInit();
        $oe->setTab($otab);
        $tmpElements[]=$oe;
    }

    foreach ($aform["elements"] as $eId => $eData) {
        if ($eId == $ini['elementid']) {

            $oe = unserialize($eData);
            $oe->interpreterInit();
            $oe->setTab($otab);
            $fn = $ini['elementfunction'];

            // now tables have the option to call the complete dataset in a single request
            if ($ajaxAllData = $_REQUEST["ajaxAllData"] ?? null) {
                header('Content-Type: application/json');

                $allRows = json_decode($ajaxAllData);
                $return = array();
                foreach ($allRows as $spanId => $row) {
                    // I will fake multiple independent requests
                    $fakeRequest = array(
                        "current_column_name" => $row->current_column_name,
                        "current_cell_value" => $row->current_cell_value,
                        "current_row_index" => $row->current_row_index,
                        "current_parameter" => $row->current_parameter,
                    );

                    $_REQUEST = $fakeRequest + $_REQUEST;
                    $_GET = $fakeRequest + $_GET;
                    $_POST = $fakeRequest + $_POST;

                    $result = $oe->$fn(false); // don't die in the function
                    $return[$spanId] = $result;
                }

                echo json_encode($return);

            } else {
                // normal, inefficient ajax request
                echo $oe->$fn(false); // don't die in the function
            }
            break;
        }
    }
}
