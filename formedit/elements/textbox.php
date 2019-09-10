<?php

class textbox extends basecontrol
{
    var $name="textbox";

    var $editorname="Textbox";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='HTML textbox';

    
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

        $minlength = $this->property['minlength'];
        if (is_numeric($minlength) && $minlength > 0) {
            $minlength = ' minlength="' . $minlength . '" ';
        }

        $maxlength = $this->property['maxlength'];
        if (is_numeric($maxlength) && $maxlength > 0) {
            $maxlength = ' maxlength="' . $maxlength . '" ';
        }

        if($this->property['onlyallowedcharacters']=="1")
        {

        }
    
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['readonly']=="1"?'opacity:0.5;':"").' '.($this->property['invisible']=="1"?' display:none; ':'').$this->property['style'].'">
            <input 
                data-customerid="'.$this->getCustomerId().'"
                type="textbox" 
                name="'.$this->id.'" 
                value="'.str_replace('"',"''",$value).'" 
                ' . $maxlength . ' ' . $minlength . '
                style="vertical-align:middle; width:100%; height:100%; box-sizing:border-box; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').'"
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'readonly="readonly"':"").'
            >
        </div>';
        return $e;
    }

    public function interpreterProve($table, $colindex, $indexvalue)
    {
        //If is not readonly and passed the parent validations.
        $ret = parent::interpreterProve($table, $colindex, $indexvalue);
        if ($this->property['readonly'] != "1" && $ret == false) {
            $value = $this->getInterpreterRequestValue();

            if ($value === '') {
                return false;
            }

            if($this->property['onlyallowedcharacters']=="1")
            {
                $sAllowed = $this->property['allowedcharacters'];
                $aAllowed=[];
                for($x=0;$x<strlen($sAllowed);$x++)
                {
                    $c = substr($sAllowed,$x,1);
                    $aAllowed[]=$c;
                }
                for($x=0;$x<strlen($value);$x++)
                {
                    $c = substr($value,$x,1);
                    if(!in_array($c,$aAllowed,true))
                    {
                        return [$this->id => "'$value' only allowed characters ($sAllowed)"];
                    }
                }
            }

            //If is not an allowed value and the min. length is defined as numeric bigger than 0
            $minlength = $this->property['minlength'];
            if (is_numeric($minlength) && $minlength > 0) {
                //Validate allowed strings
                $ignorelength = $this->property['ignorelength'];

                //Explode the ignore values and convert all of them to uppercase.
                $ignorelength_values = explode('|', $ignorelength);
                $ignorelength_values = array_map(function ($ignore_value) {
                    return strtoupper($ignore_value);
                }, $ignorelength_values);

                $allowed = false;
                if (in_array(strtoupper($value), $ignorelength_values)) {
                    $allowed = true;
                }

                $length = strlen($value);
                if (!$allowed && $length < $minlength) {
                    return [$this->id => "'$value' should have at least $minlength chars."];
                }
            }
        }

        return $ret;
    }

    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        $html .= parent::getEditorProperty_Textbox("Standardtext", 'standardtext');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Required", 'pflichtfeld');
        $html .= parent::getEditorProperty_Textbox("Errormessage", 'fehlermeldung', 'is required');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textbox("Min. length", 'minlength');
        $html .= parent::getEditorProperty_Textbox("Max. length", 'maxlength');
        $html .= parent::getEditorProperty_Textbox("<b>Allowed value(s)</b><br>Ignore the string length for this list of values. Please, separate them with a pipe.", 'ignorelength');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Only allowed characters", 'onlyallowedcharacters');
        $html .= parent::getEditorProperty_Textbox("Characters which allowed to type in the textbox, case sensitive (e.g. 0123456789)", 'allowedcharacters');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Readonly", 'readonly');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorPropertyFooter(
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            true
        );

        return $html;
    }
    
	function getSQL($table)
	{
	    $hsConfig = getHsConfig();
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
            
        $maxlength=$this->property['maxlength'];
        if(trim($maxlength)=="" || is_numeric($maxlength)==false)
            $maxlength=250;
            
        $sqlstring="alter table `".$table."` add column `".$dbfield."` VARCHAR(".$maxlength.") DEFAULT '' ";
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
