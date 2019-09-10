<?php
require_once __DIR__."/../../../../bootstrap.php";

$oxConfig = oxRegistry::getConfig();
$oxSession = oxRegistry::getSession();
$oxSession->setAdminMode(true);
if(!$oxSession->getUser())
{
    die("");
}


set_time_limit(0);
ini_set("memory_limit","400M");
ini_set("max_execution_time","3600");
ini_set("default_socket_timeout","3600");

$this->urlroot     = $oxConfig->getConfigParam('sShopURL') . "/modules/rs/formedit/formedit/";
$this->dirroot     = $oxConfig->getConfigParam('sShopDir') . "/modules/rs/formedit/formedit/";
$this->sShopDir = $oxConfig->getConfigParam('sShopDir')."/";
$this->sShopURL  = $oxConfig->getConfigParam('sShopURL')."/";

$this->dbhost = $oxConfig->getConfigParam('dbHost');
$this->dbport = $oxConfig->getConfigParam('dbPort');
$this->dbname = $oxConfig->getConfigParam('dbName');
$this->dbuser = $oxConfig->getConfigParam('dbUser');
$this->dbpass = $oxConfig->getConfigParam('dbPwd');
$this->dbutf8 = 1;

$this->formeditFolderName = "formeditprojects";
$this->modulesFolder = $oxConfig->getConfigParam('sShopDir') . "/modules/";
