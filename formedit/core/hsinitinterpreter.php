<?php

// Setting error reporting mode
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);

include_once __DIR__ . "/hsconfig.php";

set_time_limit(0);
ini_set("memory_limit","400M");
ini_set("max_execution_time","3600");
ini_set("default_socket_timeout","3600");

$hsConfig = hsconfig::getInstance();
$hsConfig->startSessionHandling();

//init config class
include_once __DIR__ . "/hsproperty.php";
include_once __DIR__ . "/hsbaseelement.php";
include_once __DIR__ . "/hsbasecontrol.php";
include_once __DIR__ . "/hsbasetab.php";
include_once __DIR__ . "/../inc/shopinterface.php";
require_once __DIR__ . "/../inc/cpffileparser.php";
require_once __DIR__ . "/../inc/commonincludes.php";

function getHsBasePath()
{
    return __DIR__ . '/../';
}

/**
 * @return hsconfig|null
 */
function getHsConfig()
{
  return hsconfig::getInstance();
}
