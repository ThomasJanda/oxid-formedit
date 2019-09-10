<?php

class hairline extends basecontrol
{
    var $name="hairline";

    var $editorname="Horizontal linie";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Html tag hr';

    public function getInterpreterRender()
    {

        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.($this->property['fixwidth'] == "0" ? "calc(100% - " . ($this->left * 2) . "px)" : "{$this->width}px").'; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').($this->property['css'] ?: $this->property['style']).'">
            <!--<hr>-->
            <div style="position:absolute; height:1px; border-top:1px solid #cccccc; left:0px; right:0px; top:'.round(($this->height / 2),0).'px; "></div> 
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html .= parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}

?>