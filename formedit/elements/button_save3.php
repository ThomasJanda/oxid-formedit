<?php

class button_save3 extends basecontrol
{
    var $name="button_save3";

    var $editorname="Save with forwarding and session variable";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button of submitting the form and initiates the save operation. After the data store, can be switched to another form, which transfers a value from the session as INDEX1';

    public function getInterpreterRender()
    {
        $hsConfig =
        $sFormularId=$this->property['loadformular'];
        $sFormularId=$this->getSelectedFormular($sFormularId);
        if($sFormularId=="")
        {
            return "No FormID specified";
        }

        return '<input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button
        data-hasparentcontrol="'.$this->getParentControl().'"  
        type="button" 
        id="'.$this->id.'" 
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        data-customeridbox="'.$this->getCustomerId().'"
        style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'"
        onclick="
            $(\'#'.$this->id.'\').attr(\'disabled\', \'disabled\');
            $(\'#'.$this->id.'\').button({ disabled: true });

            $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
            $(\'#formularid\').val(\''.$sFormularId.'\'); 
            $(\'#buttonsave3'.$this->id.'\').val(\'1\');
            $(\'#formular\').submit();
        "
        >'.$this->property['bezeichnung'].'</button>
        <input type="hidden" id="buttonsave3'.$this->id.'" name="buttonsave3'.$this->id.'" value="0">
        <script type="text/javascript"> 
            $("#'.$this->id.'").button(); 
        </script>';
        
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
        if(isset($_REQUEST['buttonsave3'.$this->id]) && $_REQUEST['buttonsave3'.$this->id]=="1")
        {
            $sessionvariable=$this->property['sessionvariable'];
            //echo $sessionvariable;
            $sessionvariable=explode(".",str_replace("#","",trim($sessionvariable)));

            $wert=$_SESSION["interpreter"]['interfromulardata'][$sessionvariable[1].".".$sessionvariable[2]];

            global $index1value;
            global $index2value;
            
            global $isfirstedit;
            $isfirstedit=true;
            global $newNavi;
            $newNavi = "EDIT_SAVE";
                
            if($this->property['writeindex']==1)
            {
                $index1value=$wert;
            }
            elseif($this->property['writeindex']==2)
            {
                $index2value=$wert;
            }
            elseif($this->property['writeindex']==3)
            {
                $index1value=$wert;
                $index2value=$wert;
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
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should be loaded when the button was pressed and that was successfully save? (ID of the form)",'loadformular','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Textbox("Session-variable (#SESSION.formularid.elementid#)",'sessionvariable');
        $html.=parent::getEditorProperty_Selectbox("In which variable should the value from the session write to?",'writeindex',array(0=>'-',1=>'#INDEX1#',2=>'#INDEX2#',3=>'#INDEX1# und #INDEX2'),0);
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>
