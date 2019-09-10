<?php

class hidden_iframe extends basecontrol
{
    var $name="hidden_iframe";

    var $editorname="Hidden iframe";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible box that manages the transfer parameter "INDEX2" of the iFrame control.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $e = '<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$hsconfig->getIndex2Value().'">';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        //$html.=parent::getEditorPropertyFooter(true,true,false,false);
        $html.=parent::getEditorPropertyFooter(
            true,
            true,
            false,
            false,
            true,
            true,
            true,
            false,
            false,
            true
        );
        return $html;
    }
}

?>