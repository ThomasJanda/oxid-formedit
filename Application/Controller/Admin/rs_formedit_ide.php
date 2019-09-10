<?php

namespace rs\formedit\Application\Controller\Admin;

class rs_formedit_ide extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    public function render()
    {
        parent::render();
        return "rs_formedit_ide.tpl";
    }
}