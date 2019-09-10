<?php

class spacer extends basecontrol
{
    var $name="spacer";

    var $editorname="Spacer";
    var $editorcategorie="Style";
    var $editorshow=true;

    public function getInterpreterRender()
    {
        $e = '<div class="'.$this->property['classname'].'" id="'.$this->id.'" style="text-align:right; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; ">
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(false,false,false,false);
        return $html;
    }
}

?>