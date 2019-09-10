<?php

class button_cancel extends basecontrol
{
    var $name="button_cancel";

    var $editorname="Cancel";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button ends the form input.';

    public function getInterpreterRender()
    {
        $hsconfig    = getHsConfig();
        $sFormularId = $this->property['loadformular'];
        $sFormularId = $this->getSelectedFormular($sFormularId);
        if($sFormularId=="")
        {
            return "No FormID specified";
        }

        $css = $this->property['style'] ?? "";

        $e='
        <div
        data-hasparentcontrol="'.$this->getParentControl().'"
        data-customeridbox="'.$this->getCustomerId().'"
        style="'.$this->getParentControlCss().' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px;'.
            'width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').$css.'">

        <input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button ';

        if($this->property['disabled']=='1')
            $e.=' disabled="disabled" ';

        $e.='
        type="button"
        id="'.$this->id.'" 
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        style=" '.$this->property['css'].' position:relative; width:'.$this->width.'px; height:'.$this->height.'px; "
        onclick="execute_'.$this->id.'(); ">'.$this->property['bezeichnung'].'</button>
        <script type="text/javascript">

            function execute_'.$this->id.'()
            {
                var ok=true;
                ';

                if($this->property['displayconfirmpopup']=="1")
                {
                    $e.='
                    var ok=false;
                    var text="'.str_replace('"',"'",$this->property['confirmpopuptext']).'";
                    ok=confirm(text);
                    ';
                }

        if ($hsconfig->getRedirectValue()) {
            $url = $_SERVER['HTTP_HOST'] . "/" . $hsconfig->getRedirectValue();
            $e   .= "window.top.location.href = '//$url';";
        } else {
            $e .= 'if(ok)
                {
                    $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
                    $(\'#indexvalue\').val(\'\');
                    $(\'#formularid\').val(\''.$sFormularId.'\');
                    $(\'#navi\').val(\'\');
                    $(\'#formular\').submit();
                }';
        }

        $e .= '
            }
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
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung','Cancel');
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should be loaded when the button was pressed? (ID of the form)",'loadformular','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Display confirm popup before execute",'displayconfirmpopup');
        $html.=parent::getEditorProperty_Textbox("Text in the confirmation popup",'confirmpopuptext');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Disabled",'disabled');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>
