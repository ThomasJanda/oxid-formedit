<?php

class hidden extends basecontrol
{
    var $name="hidden";

    var $editorname="Hidden";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible input field that can insert default values ??in the database.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $value=$this->property['standardtext'];
        $value=$hsconfig->parseSQLString($value);
        
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.$this->property['css'].'">';
        $e.= '<input 
        type="hidden" 
        name="'.$this->id.'" 
        value="'.$value.'"
        data-customerid="'.$this->getCustomerId().'" 
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
        $html.=parent::getEditorProperty_Textbox("Standardtext (Allowed variables: #INDEX1#, #INDEX2#, #KENNZEICHEN1#)",'standardtext');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,true,false,false);
        return $html;
    }
}

?>