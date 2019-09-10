<?php

class emailbox extends basecontrol
{
    var $name="emailbox";

    var $editorname="E-Mailbox";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Textbox with email validation';

    var $regEmailJS = "/^([\w+\-.])+\@([\w\-.])+\.([A-Za-z]{2,64})$/i";
    var $regEmailPHP = "/^([\w+\-.])+\@([\w\-.])+\.([A-Za-z]{2,64})$/i";

    public function interpreterProve($table, $colindex, $indexvalue)
    {
        $error="";

        $sValue = trim(parent::getInterpreterRequestValue());
        if($this->property['pflichtfeld']=='1' && $sValue=="")
        {
            $error=$this->property['fehlermeldung'];
        }
        else
        {
            if($sValue!="")
            {
                $aValues = explode(";",$sValue);
                foreach($aValues as $sEmail)
                {
                    $sEmail = trim($sEmail);
                    if($sEmail!="")
                    {
                        if(!preg_match($this->regEmailPHP,$sEmail))
                        {
                            $error = "Email wrong format (".$sEmail.")";
                            //echo $error;
                            break;
                        }
                    }
                }
            }
        }
        //die("");
        if($error!="")
            return array($this->id => $error);
        return false;
    }
    
    public function getInterpreterRender()
    {
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
            $value=$this->property['standardtext'];
        }
        else
        {
            $value=parent::getInterpreterRequestValue();
        }
    
        $maxlength=$this->property['maxlength'];
        if(is_numeric($maxlength) && $maxlength>0)
        {
            $maxlength=' maxlength="'.$maxlength.'" ';
        }
    
    
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                data-customerid="'.$this->getCustomerId().'" 
                type="textbox" 
                id="textbox'.$this->id.'" 
                name="'.$this->id.'" 
                value="'.$value.'" 
                '.$maxlength.'
                style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' "
                tabindex="'.$this->property['taborder'].'"
            >
            <script type="text/javascript">
            
                $("#textbox'.$this->id.'").blur(function() {
                
                    $(this).css("border-color","inherit");
                    /*var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;*/
                    var emailReg = '.$this->regEmailJS.';

                    var emailaddressVal = $("#textbox'.$this->id.'").val();

                    ';
                    if($this->property['morethanone']=="1")
                    {
                        $e.='
                            var result="";
                            var aemailaddressVal=emailaddressVal.split(";");
                            var bOverride = true;
                            for(var x=0;x<aemailaddressVal.length;x++)
                            {
                                var tmp=aemailaddressVal[x];
                                tmp=tmp.trim();
                                if(tmp != "") {
                                    if(!emailReg.test(tmp)) 
                                    {
                                        $(this).css("border-color","red");
                                        bOverride=false;
                                    }
                                    else
                                    {
                                        if(result!="")
                                        {
                                            result+="; ";
                                        }
                                        result+=tmp;
                                    }
                                }
                            }
                            if(bOverride)
                            {
                                $(this).val(result);
                            }
                        ';
                    }
                    else
                    {
                        $e.='
                        if(emailaddressVal == "") {
                            /* do nothing */
                        }
                        else if(!emailReg.test(emailaddressVal)) 
                        {
                            /* $("#textbox'.$this->id.'").val(""); */
                            $(this).css("border-color","red");
                        }
                        ';
                    }
                    $e.='
                });

            </script>
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Standard",'standardtext');
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Max. length",'maxlength');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("More than one email address allowed (separate with ;)",'morethanone');
        //$html.=parent::getEditorProperty_Textbox("Fehlermeldung, wenn keine g&uuml;ltige eMail-Adresse",'fehlermeldung2','Ist keine g&uuml;ltige eMail-Adresse');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
}

?>