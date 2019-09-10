<?php

class label_description extends basecontrol
{
    var $name = "label_description";

    var $editorname = "Label Description";
    var $editorcategorie = "Style";
    var $editorshow = true;
    var $editordescription = 'Simple label with display html code.';


    public function getInterpreterRender()
    {
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" 
        data-hasparentcontrol="'.$this->getParentControl().'" 
        class="'.$this->property['classname'].'" 
        id="'.$this->id.'" 
        style="'.$this->getParentControlCss().'
            '.$this->property['css'].' 
            position:absolute; 
            left:'.$this->left.'px; 
            top:'.$this->top.'px; 
            width:'.$this->width.'px; 
            height:'.$this->height.'px; 
            line-height:15px; 
            '.$this->property['style'].' 
            '.($this->property['invisible']=="1"?' display:none; ':'').'
        ">'.$this->property['text'].'</div>';
        return $e;
    }

    public function getEditorRender($text = "")
    {
        $sText = '<div style="line-height:16px;position: absolute;left: 0px;right: 0px;bottom: 0px;top: 0px;overflow: hidden; ">'.$this->property['text'].'</div>';
        return parent::getEditorRender($sText);
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textarea("HTML code",'text');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}