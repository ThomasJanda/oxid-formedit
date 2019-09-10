<?php

/**
 * Class shopInterface
 *
 * This class contains methods that their implementation differ from BASE to the Shops, but their result should be
 * the same. This file will have different contents in BASE than in the Shops.
 */
class shopInterface
{
    # region singleton
    private static $_instance = null;
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }
    #endregion

    public function getModulesDir()
    {
        return $this->getShopRootDir() . "/modules/";
    }

    public function getModulesUrl()
    {
        $hs = hsconfig::getInstance();
        $baseUrl = $hs->sShopURL;
        return rtrim($baseUrl, "/") . "/modules/";
    }

    public function getShopRootDir()
    {
        $hs = hsconfig::getInstance();
        $baseDir = $hs->sShopDir;
        return rtrim($baseDir, "/");
    }

    public function getShopRootUrl()
    {
        $hs = hsconfig::getInstance();
        $baseDir = $hs->sShopURL;
        return rtrim($baseDir, "/");
    }




    public function setGlobalParameter($sName, $sValue)
    {
        oxRegistry::getConfig()->setGlobalParameter($sName, $sValue);

    }

    public function getGlobalParameter($sName)
    {
        return oxRegistry::getConfig()->getGlobalParameter($sName);
    }
}
