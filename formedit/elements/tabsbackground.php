<?php

class tabsbackground extends basecontrol
{
    public $name="tabsbackground";

    public $editorname="Tabs Background";
    public $editorcategorie="Style";
    public $editorshow=true;

    public function interpreterInit()
    {
        parent::interpreterInit();
    }
    
    public function getEditorRender($text="")
    {
        $csswidth = "width:".$this->width."px;";

        /*resize:both; overflow:scroll; overflow-y: hidden; overflow-x: hidden;*/
        $e = '<div data-zindex="0" 
            class="element" 
            id="'.$this->id.'" style="left:'.$this->left.'px; top:'.$this->top.'px; '.$csswidth.' height:'.$this->height.'px; border:1px solid black; z-index:0;  ">
        <input type="hidden" name="classname" value="'.get_class($this).'">
        <input type="hidden" name="containerid" value="'.$this->containerid.'">';
        $e.='&nbsp;'.($text!=""?$text." (":"").$this->editorcategorie.' - '.$this->editorname.($text!=""?")":"");
        $e.= '</div>';
        return $e;
    }


    public function getInterpreterRender()
    {

        $csswidth = "width:".$this->width."px;";
        if($this->property['fixwidth']=="0")
        {
            $csswidth = "width:calc(100% - ".($this->left * 2)."px);";
        }

        $e = '<div 
            data-customerid="'.$this->getCustomerId().'" 
            class="elementtab '.$this->property['classname'].'" 
            id="'.$this->id.'" 
            style="position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; '.$csswidth.' height:'.$this->height.'px; border:1px solid black; z-index:-1; ' . ($this->property['css'] ?: $this->property['style']) .' ">
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();

        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');

        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }
}

?>