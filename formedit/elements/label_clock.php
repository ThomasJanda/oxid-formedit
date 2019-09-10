<?php

class label_clock extends basecontrol
{
    var $name="label_clock";

    var $editorname="Label clock";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Simple text field which always output the current database server time';

    public function getInterpreterRender()
    {   
        $hsconfig=getHsConfig();
        $bezeichnung="";


        $sqlstring="select now()";
        $bezeichnung = $hsconfig->getScalar($sqlstring);
        
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().'position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.$this->property['css'].' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            '.$bezeichnung.'
            '.($this->property['debugmode']=="1"?'<br>'.$sqlstring:'').'
        </div>';
        return $e;
    } 
 
    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("CSS-Style",'css');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>