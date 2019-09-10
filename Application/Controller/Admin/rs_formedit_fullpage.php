<?php
namespace rs\formedit\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Request;

class rs_formedit_fullpage extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    public function render()
    {
        parent::render();

        $request = oxNew(Request::class);
        $this->_aViewData['formedit_project']=$request->getRequestEscapedParameter("project");
        $this->_aViewData['formedit_index1']=$request->getRequestEscapedParameter("index1value");
        $this->_aViewData['formedit_index2']=$request->getRequestEscapedParameter("index2value");
        $this->_aViewData['formedit_language']=$this->_iEditLang;
        $this->_aViewData['formedit_navi']=$request->getRequestEscapedParameter("navi");

        return "rs_formedit_fullpage.tpl";

    }
}
