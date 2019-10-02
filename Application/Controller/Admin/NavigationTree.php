<?php
namespace rs\formedit\Application\Controller\Admin;

class NavigationTree extends NavigationTree_parent
{
    /**
     * handle parameter for half pages
     *
     * @param $sId
     * @param $iAct
     */
    public function rsformedit_setTabsParameter($sId, $iAct)
    {
        $oNodeList = $this->getTabs($sId, $iAct, false);

        $iAct = ($iAct > $oNodeList->length) ? ($oNodeList->length - 1) : $iAct;
        if ($oNodeList->length && ($oNode = $oNodeList->item($iAct))) {
            $sParam = $oNode->getAttribute('clparam');
            if($sParam!="")
            {
                $tmp=array();
                if(strpos($sParam,"&amp;")!==false)
                    $tmp=explode("&amp;",$sParam);
                else
                    $tmp=explode("&",$sParam);

                for($x=0;$x<count($tmp);$x++)
                {
                    $tmp2=explode("=",$tmp[$x]);

                    $_GET[$tmp2[0]]=$tmp2[1];
                }
            }
        }
    }

    /**
     * add menu.xml from the formedtiprojects folders
     * @return array
     */
    protected function _getMenuFiles()
    {
        $aFilesToLoad=parent::_getMenuFiles();

        $sPath = getShopBasePath();
        $oModulelist = oxNew('oxmodulelist');
        $aActiveModuleInfo = $oModulelist->getActiveModuleInfo();
        if (is_array($aActiveModuleInfo)) {
            foreach ($aActiveModuleInfo as $sModulePath) {
                $sFullPath = $sPath . "modules/" . $sModulePath;
                // missing file/folder?
                if (is_dir($sFullPath)) {
                    // including menu file
                    $sMenuFile = $sFullPath . "/formeditprojects/menu.xml";
                    if (file_exists($sMenuFile) && is_readable($sMenuFile)) {
                        $aFilesToLoad[] = $sMenuFile;
                    }
                }
            }
        }
        return $aFilesToLoad;
    }
}