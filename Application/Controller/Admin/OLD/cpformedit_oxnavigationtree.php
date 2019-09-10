<?php 

class cpformedit_oxnavigationtree extends cpformedit_oxnavigationtree_parent
{
    
    /*
    protected function _addLinks($oDom)
    {
        $sURL = 'index.php?'; // session parameters will be included later (after cache processor)
        $oXPath = new DomXPath($oDom);

        // building
        $oNodeList = $oXPath->query("//SUBMENU[@type]");
        foreach ($oNodeList as $oNode) {
            // fetching class
            $sType = $oNode->getAttribute('type');
            $sCl="";
            if($sType=="half")
                $cCl="cpformedit_halfpage";            
            elseif($sType=="half_lang")
                $cCl="cpformedit_halfpage_lang";            
            elseif($sType=="full")
                $cCl="cpformedit_fullpage";            
            elseif($sType=="full_lang")
                $cCl="cpformedit_fullpage_lang";
                
            $sCl = $sCl ? "cl=$sCl" : '';

            // fetching params
            $sParam = $oNode->getAttribute('clparam');
            $sParam = $sParam ? "&$sParam" : '';

            // setting link
            $oNode->setAttribute('link', "{$sURL}{$sCl}{$sParam}");
        }
    }    
    */
    
    public function cpformedit_setTabsParameter($sId, $iAct)
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
                    $sMenuFile = $sFullPath . "/cpformedit/menu.xml";
                    if (file_exists($sMenuFile) && is_readable($sMenuFile)) {
                        //echo $sMenuFile;
                        //echo '<br>';
                        //die("");
                        $aFilesToLoad[] = $sMenuFile;
                    }
                }
            }
        }        
        return $aFilesToLoad;
    }
}