<?php

class hairline_vertical extends basecontrol
{
    var $name="hairline_vertical";

    var $editorname="Vertical linie";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Html tag hr';

    public function getInterpreterRender()
    {

        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <!--<hr>-->
            <div style="position:absolute; width:1px; border-left:1px solid #cccccc; top:0px; bottom:0px; left:'.round(($this->width / 2),0).'px; "></div> 
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}

?>