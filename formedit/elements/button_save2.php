<?php

class button_save2 extends basecontrol
{
    var $name="button_save2";

    var $editorname="Save with forwarding";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button of submitting the form and initiates the save operation. After the storing the data, another form gets load, which gets transferred the current index.';

    public function getInterpreterRender()
    {
        $sFormularId=$this->property['loadformular'];
        $sFormularId=$this->getSelectedFormular($sFormularId);
        if($sFormularId=="")
        {
            return "No FormID specified";
        }

        $e='<input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button data-hasparentcontrol="'.$this->getParentControl().'" ';
        if($this->property['disabled']=="1")
            $e.=' disabled="disabled" ';
        $e.=' type="button" 
        id="'.$this->id.'" 
         data-customeridbox="'.$this->getCustomerId().'"
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'"
        onclick="
            $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
            UpdateStatus_'.$this->id.'();            
        "
        >'.$this->property['bezeichnung'].'</button>
        <input type="hidden" id="buttonsave2'.$this->id.'" name="buttonsave2'.$this->id.'" value="0">
        <script type="text/javascript"> 
            $("#'.$this->id.'").button(); 
            function UpdateStatus_'.$this->id.'()
            {
                $(\'#'.$this->id.'\').attr(\'disabled\', \'disabled\');
                $(\'#'.$this->id.'\').button({ disabled: true });

                $(\'#formularid\').val(\''.$sFormularId.'\'); 
                $(\'#buttonsave2'.$this->id.'\').val(\'1\');
                $(\'#formular\').submit();
            }
        </script>';

        echo $e;

        //}
        
    }
    

    public function interpreterFinishedSaveNew()
    {
        $this->interpreterFinishedSave();
    }
    public function interpreterFinishedSaveEdit()
    {
        $this->interpreterFinishedSave();
    }
    public function interpreterFinishedSave()
    {
        if (isset($_REQUEST['buttonsave2' . $this->id]) && $_REQUEST['buttonsave2' . $this->id] == "1") {

            global $index1value;
            global $index2value;

            global $isfirstedit;
            $isfirstedit = true;
            global $newNavi;
            $newNavi = "EDIT_SAVE";

            if ($this->property['writeindex'] == 1) {
                $index1value = $_REQUEST['index1value'];
            } elseif ($this->property['writeindex'] == 2) {
                $index2value = $_REQUEST['index1value'];
            } elseif ($this->property['writeindex'] == 3) {
                $index1value = $_REQUEST['index1value'];
                $index2value = $_REQUEST['index1value'];
            }
        }
    }
    
    

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung', 'Save');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should be loaded when the button was pressed and the data was successfully save? (ID of the form)",'loadformular','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Selectbox("In which variable the current index should be written",'writeindex',array(0=>'-',1=>'#INDEX1#',2=>'#INDEX2#',3=>'#INDEX1# und #INDEX2'),0);
        $html.=parent::getEditorProperty_Checkbox("Disabled",'disabled');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>
