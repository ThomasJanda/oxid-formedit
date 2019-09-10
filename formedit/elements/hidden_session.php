<?php

class hidden_session extends basecontrol
{
    var $name="hidden_session";

    var $editorname="Hidden Session";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible input field that can insert values ??from the session in the database.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $hsconfig=getHsConfig();
        $value=$_SESSION["interpreter"]['interfromulardata'][$this->property['formularcustomerid'].".".$this->property['elementcustomerid']];

        $e = '<div data-customerid="'.$this->getCustomerId().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.$this->property['css'].'">';
        $e.= '<input 
        data-customerid="'.$this->getCustomerId().'" 
        type="hidden" 
        name="'.$this->id.'" 
        value="'.$value.'"
        >';
        if($this->property['debugmode']=="1") $e.=$this->property['bezeichnung']."=".$value;
        $e.= '</div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox('Title','bezeichnung');
        $html.=parent::getEditorProperty_Textbox('Form Customer ID','formularcustomerid');
        $html.=parent::getEditorProperty_Textbox('Element Customer ID','elementcustomerid');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,true,false,false);
        return $html;
    }
}

?>