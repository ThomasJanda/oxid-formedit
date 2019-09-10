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
            width:'.$this->width.'px; 
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
        //$html.=parent::getEditorProperty_Textbox("CSS-style",'style');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>