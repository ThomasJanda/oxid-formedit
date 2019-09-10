<?php

// Setting error reporting mode
error_reporting( E_ALL ^ E_NOTICE );

require_once __DIR__ . "/hsconfig.php";

/**
 * @return hsconfig|null
 */
function getHsConfig()
{
    return hsconfig::getInstance();
}

function getHsBasePath()
{
    return dirname(__FILE__).'/../';
}

$hsConfig = getHsConfig();
$hsConfig->setEditorMode(true);
$hsConfig->startSessionHandling();

//prove if in post, get, request all variables present
$raw = file_get_contents("php://input");
$sep = ini_get('arg_separator.input');
$raw = explode($sep,$raw);
foreach ($raw as $r) {
    $tmp = explode("=", $r);
    if (isset($tmp[1]) && $tmp[1] == "") {
        $_REQUEST[$tmp[0]] = "";
        $_POST[$tmp[0]] = "";
        $_GET[$tmp[0]] = "";
    }
}

//init configclass
include __DIR__ . "/hsproperty.php";
include __DIR__ . "/hsbaseelement.php";
include __DIR__ . "/hsbasecontrol.php";
include __DIR__ . "/hsbasetab.php";
include __DIR__ . "/../inc/shopinterface.php";



