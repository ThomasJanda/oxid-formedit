<?php

class textbox_integer extends basecontrol
{
    var $name="textbox_integer";

    var $editorname="Textbox Integer";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Allows only digits (z.B. 100,453,975)';


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
    
    
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                data-customerid="'.$this->getCustomerId().'"
                id="textbox'.$this->id.'" 
                type="textbox" 
                name="'.$this->id.'" 
                value="'.$value.'" 
                style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' '.($this->property['readonly']=="1"?'opacity:0.5;':"").' "
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'readonly="readonly"':"").'
            >
        </div>
        <script type="text/javascript">
            $(\'#textbox'.$this->id.'\').keypress(function(e) {
                //console.log(e.which);
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)
                ';
                if($this->property['allownegative']=="1")
                {
                    $e.=' && e.which!=45 ';
                }
                $e.='
                ) {
                    return false;
                }
            });
            $(\'#textbox'.$this->id.'\').blur(function() {
                if(parseFloat($(\'#textbox'.$this->id.'\').val())+""!="NaN")
                {
                    $(\'#textbox'.$this->id.'\').val(parseInt($(\'#textbox'.$this->id.'\').val()));
                }
                else
                {
                    $(\'#textbox'.$this->id.'\').val("");
                }
            });
        </script>
        ';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Standardtext",'standardtext');
        $html.=parent::getEditorProperty_Checkbox("Allow negative digits",'allownegative');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }

	function getSQL($table)
	{
	    $hsConfig = getHsConfig();
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		$sqlstring = "alter table `".$table."` add column `".$dbfield."` INT(11) DEFAULT 0 ";
        $dbfielddescription = trim($this->property['datenbankspaltebeschreibung']);
        if($dbfielddescription!="")
        {
            $sqlstring.=" COMMENT '".$hsConfig->escapeString($dbfielddescription)."'";
        }
        $sqlstring.="; ";
        return $sqlstring;
	}
}

?>