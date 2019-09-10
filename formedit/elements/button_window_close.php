<?php

class button_window_close extends basecontrol
{
    var $name="button_window_close";

    var $editorname="Close";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button that close the browser tab';

    public function getInterpreterRender()
    {

        $e='
        <div
        data-hasparentcontrol="'.$this->getParentControl().'"
        data-customeridbox="'.$this->getCustomerId().'"
        style="'.$this->getParentControlCss().'  position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">

        <input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button ';

        if($this->property['disabled']=='1')
            $e.=' disabled="disabled" ';

        $e.='
        type="button"
        id="'.$this->id.'"
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'"
        style="'.$this->property['css'].' position:relative; width:'.$this->width.'px; height:'.$this->height.'px; "
        onclick="
            window.close();
        ">'.$this->property['bezeichnung'].'</button>
        <script type="text/javascript">
            $("#'.$this->id.'").button();
        </script>
        </div>';

        return $e;
    }

    public function getEditorRender($text = '')
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Checkbox("Disabled",'disabled');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>