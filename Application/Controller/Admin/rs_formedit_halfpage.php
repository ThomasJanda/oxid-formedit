<?php
namespace rs\formedit\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Request;

class rs_formedit_halfpage extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{

    public function render()
    {
        parent::render();
        $oConfig = $this->getConfig();
        $sOxId = $this->getEditObjectId();

        $request = oxNew(Request::class);

        //load tab parameter
        $myAdminNavigation = $this->getNavigation();
        $iActTab = $request->getRequestEscapedParameter('rs_formedit_sPos');
        $sNode   = $request->getRequestEscapedParameter('rs_formedit_sNode');
        
        // set tabs parameter
        $myAdminNavigation->rsformedit_setTabsParameter($sNode, $iActTab);
        
        $useindex= oxRegistry::getConfig()->getRequestParameter('useindex');
        if($useindex=="")
            $useindex="index1value";
        
        $this->_aViewData['formedit_spos']=$iActTab;
        $this->_aViewData['formedit_snode']=$sNode;
        
        $this->_aViewData['formedit_project']=$request->getRequestEscapedParameter("project");
        $this->_aViewData['formedit_index1']=($useindex=="index1value"?$sOxId:'');
        $this->_aViewData['formedit_index2']=($useindex=="index2value"?$sOxId:'');
        $this->_aViewData['formedit_language']=$this->_iEditLang;
        $this->_aViewData['formedit_navi']='EDIT';
        
        return "rs_formedit_halfpage.tpl";
    }
}
