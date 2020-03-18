<?php

class label extends basecontrol
{
    var $name="label";

    var $editorname="Label";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Simple text field that is used to label other elements';

    public function getInterpreterRender()
    {
        $csswidth = "width:".$this->width."px;";
        if($this->property['fixwidth']=="0")
        {
            $csswidth = "width:calc(100% - ".($this->left * 2)."px);";
        }
        
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" 
        data-hasparentcontrol="'.$this->getParentControl().'" 
        class="'.$this->property['classname'].'" 
        id="'.$this->id.'" 
        style="'.$this->getParentControlCss().'
            text-align:'.($this->property['textalign']!=""?$this->property['textalign']:'right').'; 
            '.$this->property['css'].' 
            position:absolute; 
            left:'.$this->left.'px; 
            top:'.$this->top.'px; 
            '.$csswidth.' 
            height:'.$this->height.'px; 
            line-height:'.$this->height.'px; 
            '.$this->property['style'].' 
            '.($this->property['invisible']=="1"?' display:none; ':'').'
        ">'.$this->property['bezeichnung'].'</div>';
        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Selectbox("Alignment",'textalign', ['' => 'Right', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right'], '');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>