<?php

class textbox_double extends basecontrol
{
    var $name="textbox_double";

    var $editorname="Textbox Double";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Only double values allowed (z.B. 1, 1.45, 3234.454)';

    
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
        
        if($this->property['setzerotonull']=="1" && $value==0)
        {
            $value="";
        }
    
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                data-customerid="'.$this->getCustomerId().'"
                id="textbox'.$this->id.'" 
                type="textbox" 
                name="'.$this->id.'" 
                value="'.$value.'" 
                style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px;border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' '.($this->property['readonly']=="1"?'opacity:0.5;':"").' "
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'readonly="readonly"':"").'
            >
        </div>
        <script type="text/javascript">
            $(\'#textbox'.$this->id.'\').keypress(function(e) {
                if ('.($this->property['notallownegative']=='1'?'':'e.which!=45 && ').'e.which != 8 && e.which != 0 && e.which!=46 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });
            $(\'#textbox'.$this->id.'\').blur(function() {
            
                var source=$(\'#textbox'.$this->id.'\').val();
                var target="";
                for(x=0;x<source.length;x++)
                {
                    if (source.charCodeAt(x)!=45 && source.charCodeAt(x)!= 8 && source.charCodeAt(x)!= 0 && source.charCodeAt(x)!=46 && (source.charCodeAt(x) < 48 || source.charCodeAt(x) > 57)) 
                    {}
                    else
                        target=target + source.charAt(x);
                }
                if(parseFloat(target)+""!="NaN")
                {
                    target=parseFloat(target);
                    ';
                    
                    if($this->property['round']=="1")
                    {
                        $e.='
                        var faktor;
                        var n='.$this->property['digits'].';
                        faktor = Math.pow(10,n);
                        target=(Math.round(target * faktor) / faktor);
                        ';
                    }
                    $e.='
                    $(\'#textbox'.$this->id.'\').val(parseFloat(target));
                }
                else
                {
                    $(\'#textbox'.$this->id.'\').val(\'\');
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
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Round",'round');
        $html.=parent::getEditorProperty_Selectbox("Digits",'digits',array(0,1,2,3,4,5,6,8,9,10));
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Show 0 as empty",'setzerotonull');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Not allow negative values",'notallownegative');
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

        $sqlstring = "alter table `".$table."` add column `".$dbfield."` DOUBLE DEFAULT 0 ";
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