<?php

class headline extends basecontrol
{
    var $name="headline";

    var $editorname="Headline";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Html-Tag h1';

    private static $cpfNoTitleCounter = 0;

    public function getInterpreterRender()
    {
        if (isset($_REQUEST["cpf-no-title"])) {
            if (self::$cpfNoTitleCounter === 0) {
                return "";
            }
            self::$cpfNoTitleCounter++;
        }
        
        $e = '<div data-customeridbox="' . $this->getCustomerId() . '" data-hasparentcontrol="' . $this->getParentControl() . '" class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->getParentControlCss() . '' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; ' . ($this->property['invisible'] == "1" ? ' display:none; ' : '') . '"> <h1 style="height:' . $this->height . 'px; line-height:' . $this->height . 'px; ">' . $this->property['bezeichnung'] . '</h1> </div>';

        return $e;
    }

    public function getEditorRender($text = "")
    {
        $e = '<div data-hasparentcontrol="'.$this->getParentControl().'" class="element" id="'.$this->id.'" style="'.$this->getParentControlCss().'left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
            <input type="hidden" name="classname" value="'.get_class($this).'">
            <input type="hidden" name="containerid" value="'.$this->containerid.'">
            &nbsp;<span style="font-weight:bold; font-size:14px; ">'.($this->property['bezeichnung']!=""?$this->property['bezeichnung']." (":"").$this->editorcategorie.' - '.$this->editorname.($this->property['bezeichnung']!=""?")":"").'</span>
        </div>';
        return $e;
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}

?>