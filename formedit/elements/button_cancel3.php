<?php

class button_cancel3 extends basecontrol
{
    var $name="button_cancel3";

    var $editorname="Cancel with forwarding and session variable";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button ends the form input. After canceling can be switched to another form, which transfers a value from the session as INDEX1';

    public function getInterpreterRender()
    {
        $sFormularId=$this->property['loadformular'];
        $sFormularId=$this->getSelectedFormular($sFormularId);
        if($sFormularId=="")
        {
            return "No FormID specified";
        }

        $sessionvariable=$this->property['sessionvariable'];
        $sessionvariable=explode(".",str_replace("#","",trim($sessionvariable)));

        $wert=$_SESSION["interpreter"]['interfromulardata'][$sessionvariable[1].".".$sessionvariable[2]];
        
        $e='<input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button 
        type="button" 
        data-hasparentcontrol="'.$this->getParentControl().'" 
         data-customeridbox="'.$this->getCustomerId().'"
        id="'.$this->id.'" 
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'"
        onclick="';
            $e.=' 
            $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
            $(\'#index1value\').val(\''.$wert.'\'); 
            $(\'#index2value\').val(\'\');
            $(\'#formularid\').val(\''.$sFormularId.'\'); 
            $(\'#navi\').val(\'EDIT\'); 
            $(\'#formular\').submit(); 
        ">'.$this->property['bezeichnung'].'</button>        
        <script type="text/javascript"> 
            $("#'.$this->id.'").button(); 
        </script>';
        
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
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung','Cancel');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should be loaded when the button was pressed? (ID of the form)",'loadformular','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Textbox("Session variable (#SESSION.formularid.elementid#)",'sessionvariable');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>