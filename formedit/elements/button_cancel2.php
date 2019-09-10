<?php

class button_cancel2 extends basecontrol
{
    var $name="button_cancel2";

    var $editorname="Cancel with forwarding";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button ends the form input. After canceling can be switched to another form, which gets transferred with the current index.';

    public function interpreterInit()
    {
        // whenever this button is pressed it should force recreate cache, this button is normally used to alter data in BASE.
        if (isset($_REQUEST[$this->id]) && $_REQUEST[$this->id]) {
            $_REQUEST["forceCacheRefresh"] = true;
        }
    }

    public function getInterpreterRender()
    {
        $sFormularId=$this->property['loadformular'];
        $sFormularId=$this->getSelectedFormular($sFormularId);
        if($sFormularId=="")
        {
            return "No FormID specified";
        }

        $urlParameter = $this->property['urlparameter'] ?? "";
        $urlParameter = explode("&", $urlParameter);
        $aUrlParameter = [];
        foreach($urlParameter as $sParam)
        {
            $split = explode("=",$sParam);
            $aUrlParameter[$split[0]]=$split[1];
        }

        $css = $this->property['style'] ?? "";

        $e='<input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button 
        type="button" 
        data-hasparentcontrol="'.$this->getParentControl().'" 
         data-customerid="'.$this->getCustomerId().'"
        id="'.$this->id.'" 
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px;'.
            'width:'.$this->width.'px; height:'.$this->height.'px;'.($this->property['invisible']=="1"?' display:none; ':'').$css.'"
        onclick="
            $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
            var readindex=\'\'; ';
            
            if($this->property['readindex']=="0")
                $e.=' readindex=$(\'#index1value\').val(); ';
            if($this->property['readindex']=="1")
                $e.=' readindex=$(\'#index2value\').val(); ';
            
            if($this->property['writeindex']=="0")
                $e.=' $(\'#index1value\').val(readindex); ';
            if($this->property['writeindex']=="1")
                $e.=' $(\'#index2value\').val(readindex); ';
            if($this->property['writeindex']=="2")
            {
                $e.=' $(\'#index1value\').val(readindex); ';
                $e.=' $(\'#index2value\').val(readindex); ';
            }

            foreach($aUrlParameter as $sName => $sValue)
            {
                $e.= '
                var input = document.createElement(\'input\');
                input.setAttribute(\'type\', \'hidden\');
                input.setAttribute(\'name\', \''.$sName.'\');
                input.setAttribute(\'value\', \''.$sValue.'\');
                document.getElementById(\'formular\').appendChild(input);
                ';
            }
                
            $e.='
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
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should be loaded when the button was pressed? (ID of the form)",'loadformular','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Selectbox("From which variable should the current index to be read",'readindex',array(0=>'#INDEX1#',1=>'#INDEX2#'),0);
        $html.=parent::getEditorProperty_Selectbox("In which variable should the index written",'writeindex',array(0=>'#INDEX1#',1=>'#INDEX2#',3=>'#INDEX1# und #INDEX2'),0);
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Url-Parameter (separate by &, you can use #INDEX1#,#INDEX2#...)", 'urlparameter');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>
