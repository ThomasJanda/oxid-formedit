<?php

class checkbox extends basecontrol
{
    var $name="checkbox";

    var $editorname="Checkbox";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Simple Checkbox';

    public function getInterpreterRender()
    {
        $checked=false;
        if(parent::getInterpreterIsFirstNew() || $this->property['datenbankspalte']=="")
        {
            if($this->property['angehakt']=="1")
                $checked=true;
        }
        else
        {
            if(parent::getInterpreterRequestValue()=="1")
                $checked=true;
        }
    
    
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            ';
            if($this->property['readonly']=="1")
                $e.='<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.($checked?'1':'0').'">';
            else
                $e.='<input type="hidden" name="'.$this->id.'" value="0">';

            $e.='<input 
                ';
                if($this->property['readonly']!="1")
                {
                    $e.='data-customerid="'.$this->getCustomerId().'" ';
                }
                $e.='
                type="checkbox" 
                name="'.$this->id.'" 
                value="1" 
                '.($checked?'checked':'').' 
                style="vertical-align:middle; "
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'disabled="disabled"':"").'
                >
            '.$this->property['bezeichnung'].'
            ';
            
            $setreadonly=trim($this->property['setreadonly']);
            if($setreadonly!="")
            {
                $hsconfig = getHsConfig();
                $interpreterid = $hsconfig->getInterpreterId();
                $setreadonly=explode("\n",str_replace("\r","",$setreadonly));
                $e.='<script type="text/javascript">
                
                function enable_elements_'.$interpreterid.$this->name.$this->id.'()
                {
                    ';
                    foreach($setreadonly as $customeridbox)
                    {
                        $e.='$( "#formular div[data-customeridbox='.$customeridbox.'] .enableelement_'.$interpreterid.$this->name.$this->id.'" ).css("display","none");
                        ';
                    }
                    $e.='
                }
                function disable_elements_'.$interpreterid.$this->name.$this->id.'()
                {
                    ';
                    foreach($setreadonly as $customeridbox)
                    {
                        $e.='$( "#formular div[data-customeridbox='.$customeridbox.'] .enableelement_'.$interpreterid.$this->name.$this->id.'" ).css("display","block");
                        ';
                    }
                    $e.='
                }
                function init_elements_'.$interpreterid.$this->name.$this->id.'()
                {
                    ';
                    foreach($setreadonly as $customeridbox)
                    {
                        $e.='$( \'<div class="enableelement_clipboard enableelement_'.$interpreterid.$this->name.$this->id.'" style="position:absolute; left:0px; right:0px; top:0px; bottom:-5px; background-color:white; opacity:0.5; z-index:10000; display:none; "></div>\' ).appendTo( $( "#formular div[data-customeridbox='.$customeridbox.']" ) ); 
                        ';
                    }
                    $e.='
                }
                
                $(function() {
                    init_elements_'.$interpreterid.$this->name.$this->id.'();
                    ';
                    if($checked)
                    {
                        $e.='enable_elements_'.$interpreterid.$this->name.$this->id.'(); ';
                    }
                    else
                    {
                        $e.='disable_elements_'.$interpreterid.$this->name.$this->id.'(); ';
                    }
                    $e.='
                    
                    $("#'.$this->id.' input[type=checkbox]:checkbox").change(function () {
                        var check = $(this).attr("checked");
                        if(check=="checked")
                        {
                            enable_elements_'.$interpreterid.$this->name.$this->id.'();
                        }
                        else
                        {
                            disable_elements_'.$interpreterid.$this->name.$this->id.'();
                        }
                    });
                });
                
                </script>';
            }
            $setreadonly=trim($this->property['setreadonly2']);
            if($setreadonly!="")
            {
                $hsconfig = getHsConfig();
                $interpreterid = $hsconfig->getInterpreterId();
                $setreadonly=explode("\n",str_replace("\r","",$setreadonly));
                $e.='<script type="text/javascript">
                
                function enable_elements2_'.$interpreterid.$this->name.$this->id.'()
                {
                    ';
                    foreach($setreadonly as $customeridbox)
                    {
                        $e.='$( "#formular div[data-customeridbox='.$customeridbox.'] .enableelement2_'.$interpreterid.$this->name.$this->id.'" ).css("display","none");
                        ';
                    }
                    $e.='
                }
                function disable_elements2_'.$interpreterid.$this->name.$this->id.'()
                {
                    ';
                    foreach($setreadonly as $customeridbox)
                    {
                        $e.='$( "#formular div[data-customeridbox='.$customeridbox.'] .enableelement2_'.$interpreterid.$this->name.$this->id.'" ).css("display","block");
                        ';
                    }
                    $e.='
                }
                function init_elements2_'.$interpreterid.$this->name.$this->id.'()
                {
                    ';
                    foreach($setreadonly as $customeridbox)
                    {
                        $e.='$( \'<div class="enableelement_clipboard enableelement2_'.$interpreterid.$this->name.$this->id.'" style="position:absolute; left:0px; right:0px; top:0px; bottom:-5px; background-color:white; opacity:0.5; z-index:10000; display:none; "></div>\' ).appendTo( $( "#formular div[data-customeridbox='.$customeridbox.']" ) ); 
                        ';
                    }
                    $e.='
                }
                
                $(function() {
                    init_elements2_'.$interpreterid.$this->name.$this->id.'();
                    ';
                    if($checked)
                    {
                        $e.='disable_elements2_'.$interpreterid.$this->name.$this->id.'(); ';
                    }
                    else
                    {
                        $e.='enable_elements2_'.$interpreterid.$this->name.$this->id.'(); ';
                    }
                    $e.='
                    
                    $("#'.$this->id.' input[type=checkbox]:checkbox").change(function () {
                        var check = $(this).attr("checked");
                        if(check=="checked")
                        {
                            disable_elements2_'.$interpreterid.$this->name.$this->id.'();
                        }
                        else
                        {
                            enable_elements2_'.$interpreterid.$this->name.$this->id.'();
                        }
                    });
                });
                
                </script>';
            }
            
            $e.='
        </div>';
        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender(($text==""?$this->property['bezeichnung']:$text));
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Checkbox("Checked",'angehakt');
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("Set readonly, if unchecked. List with 'Customer IDs' from elements which should enable/disable. (One id per line)",'setreadonly');
        $html.=parent::getEditorProperty_Textarea("Set readonly, if checked. List with 'Customer IDs' from elements which should enable/disable. (One id per line)",'setreadonly2');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }

	function getSQL($table)
	{
	    $hsConfig = getHsConfig();
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		$sql = "alter table `".$table."` add column `".$dbfield."` TINYINT(1) DEFAULT 0 ";
        $dbfielddescription = trim($this->property['datenbankspaltebeschreibung']);
        if($dbfielddescription!="")
        {
            $sql.=" COMMENT '".$hsConfig->escapeString($dbfielddescription)."'";
        }
        $sql.="; ";
        return $sql;
	}
}

?>
