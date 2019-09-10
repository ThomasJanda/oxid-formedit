<?php

$hsconfig = hsConfig::getInstance();
$baseDir = $hsconfig->getBaseDir();

// including all elements inside base folder elements.
$elements = glob("$baseDir/elements/*.php");
foreach ($elements as $element) {
    require_once $element;
}

// including all files inside base folder scriptphp
require_once "$baseDir/scriptphp/interfacephp.php";
$scripts = glob("$baseDir/scriptphp/*.php");
foreach ($scripts as $script) {
    require_once $script;
}

require_once "$baseDir/sqlparser/interfacesqlparser.php";
$sqlParsers = glob("$baseDir/sqlparser/*.php");
foreach ($sqlParsers as $sqlParser) {
    if (preg_match('~interfacesqlparser.php$~', $sqlParser)) continue;
    require_once $sqlParser;
}

require_once "$baseDir/scriptvalidation/interfacevalidation.php";
$scriptValidations = glob("$baseDir/scriptvalidation/*.php");
foreach ($scriptValidations as $scriptValidation) {
    if (preg_match('~interfacevalidation.php$~', $scriptValidation)) continue;
    require_once $scriptValidation;
}
