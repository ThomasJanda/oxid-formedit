<?php

$classname = $_REQUEST['classname'];
$id = $_REQUEST['id'];
$containerid = $_REQUEST['containerid'];
if ($containerid == "" || $classname == "" || $id == "")
    die;

require_once __DIR__ . "/core/hsinit.php";
$hsconfig = getHsConfig();
$fileName = strtolower($classname);
$basePath = rtrim($hsconfig->getBasePath, "/");
require_once "$basePath/elements/$fileName.php";

$e = unserialize($_SESSION['editor'][$containerid]['elements'][$id]);
$e->setEditorProperty();
$_SESSION['editor'][$containerid]['elements'][$id] = serialize($e);

//for tab element, delete all hasparentcontrol, if tab is deleted
$files = glob($hsconfig->getBasePath . "elements/*.php");
foreach ($files as $file) include_once $file;

$tabidelement = $containerid . "_" . $id . "_";
$tabids = array();
if ($e->isparentcontrol) {
    $_SESSION[$containerid]['tabs'][$id] = 0;

    $tabs = trim($e->property['tabs']);
    if ($tabs != "") {
        $tabs = explode("|", $tabs);
        for ($x = 0; $x < count($tabs); $x++) {
            $tmpid = $containerid . "_" . $id . "_" . $x;

            $tabids[] = $tmpid;
        }
    }
}

foreach ($_SESSION["editor"] as $key => $value) {
    foreach ($_SESSION["editor"][$key]["elements"] as $key2 => $value2) {
        if ($e = unserialize($_SESSION['editor'][$key]['elements'][$key2])) {
            if ($e->getParentControl() != "") {
                if (substr($e->getParentControl(), 0, strlen($tabidelement)) == $tabidelement) {
                    if (in_array($e->getParentControl(), $tabids) == false) {
                        $e->setParentControl("");
                        $_SESSION["editor"][$key]["elements"][$key2] = serialize($e);
                    }
                }
            }
        }
    }
}
