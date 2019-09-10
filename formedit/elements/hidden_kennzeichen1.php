<?php

class hidden_kennzeichen1 extends basecontrol
{
    var $name="hidden_kennzeichen1";

    var $editorname="Hidden mark1";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible box that manages the transfer parameter "KENNZEICHEN1" of the grid control.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $e = '<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$hsconfig->getKennzeichen1Value().'">';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,true,false,false);
        return $html;
    }
}

?>