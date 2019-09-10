<?php

class button_save extends basecontrol
{
    var $name="button_save";

    var $editorname="Save";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button of submitting the form and initiates the save operation.';

    public function getInterpreterRender()
    {
        $sFormularId=$this->property['loadformular'];
        $sFormularId=$this->getSelectedFormular($sFormularId);
        if($sFormularId=="")
        {
            return "No FormID specified";
        }
        
        $e='
        <div 
        data-hasparentcontrol="'.$this->getParentControl().'" 
        data-customeridbox="'.$this->getCustomerId().'" 
        style="'.$this->getParentControlCss().' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
        <button ';
        if($this->property['disabled']=="1")
            $e.=' disabled="disabled" ';
        $e.=' type="button" 
        id="'.$this->id.'" 
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        style="'.$this->property['css'].' position:relative; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['disabled']=="1"?'cursor:pointer;':'').' "
        onclick="UpdateStatus_'.$this->id.'(); "
        >'.$this->property['bezeichnung'].'</button>
        <input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <script type="text/javascript"> 
            $("#'.$this->id.'").button({ disabled: true });
            function UpdateStatus_'.$this->id.'()
            {
                $("#'.$this->id.'").attr("disabled", "disabled");
                $("#'.$this->id.'").button({ disabled: true });

                $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
                $(\'#formularid\').val(\''.$sFormularId.'\'); 
                $(\'#formular\').submit();
            }
            ';
            if($this->property['disabled']!="1")
            {
                $e.='
                $(document).ready(function() {
                    $("#'.$this->id.'").removeAttr("disabled");
                    $("#'.$this->id.'").button({ disabled: false });
                });
                ';
            }
        $e.='</script>
        </div>';
        
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
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung','Save');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should be loaded when the button was pressed and the data was successfully save? (ID of the form)",'loadformular','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Checkbox("Disabled",'disabled');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>