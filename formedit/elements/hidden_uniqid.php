<?php

class hidden_uniqid extends basecontrol
{
    var $name="hidden_uniqid";

    var $editorname="Hidden Uniqid";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible box which can store a uniqid() value in a column. Then this value is always maintained.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
            $value=uniqid('');
        }
        else
        {
            $value=parent::getInterpreterRequestValue();
        }
        
        $e = '<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$value.'">';
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