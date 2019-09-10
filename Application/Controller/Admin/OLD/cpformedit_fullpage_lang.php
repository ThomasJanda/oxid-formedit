<?php

class cpformedit_fullpage_lang extends oxAdminDetails
{
    public function render()
    {
        parent::render();

        $this->_aViewData['formedit_project']=oxRegistry::getConfig()->getRequestParameter("project");
        $this->_aViewData['formedit_index1']=oxRegistry::getConfig()->getRequestParameter("index1value");
        $this->_aViewData['formedit_index2']=oxRegistry::getConfig()->getRequestParameter("index2value");
        $this->_aViewData['formedit_language']=$this->_iEditLang;
        $this->_aViewData['formedit_navi']=oxRegistry::getConfig()->getRequestParameter("navi");

        return "cpformedit_fullpage_lang.tpl";
    }
}
