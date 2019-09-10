<?php

class textbox_select extends basecontrol
{
    var $name="textbox_select";

    var $editorname="Textbox select";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='HTML textbox with select box option';


    public function getInterpreterRender()
    {
        $hsConfig = getHsConfig();
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

        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['readonly']=="1"?'opacity:0.5;':"").' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                id="'.$this->id.'_textbox"
                data-customerid="'.$this->getCustomerId().'"
                type="textbox" 
                name="'.$this->id.'" 
                list="'.$this->id.'_datalist"
                value="'.str_replace('"',"''",$value).'" 
                '.$maxlength.'
                style="vertical-align:middle; width:100%; height:100%; box-sizing:border-box; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').'"
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'readonly="readonly"':"").'
            >
            <input 
                type="hidden" 
                id="'.$this->id.'_hidden"
                data-customerid="'.$this->getCustomerId().'_hidden"
                name="'.$this->id.'" 
                value="'.str_replace('"',"''",$value).'"
            >';

            $sqlstring=$this->property['standardsqlstring'];
            $sqlstring = $hsConfig->parseSQLString($sqlstring);
            if($this->property['debugmode']=="1")
            {
                $e.='<div><b>Select:</b><br>'.htmlentities($sqlstring).'</div>';
            }

            $e.='<datalist id="'.$this->id.'_datalist">';

            $db = $hsConfig->getDbId();
            if($rs = $db->query($sqlstring)) {
                while ($row = $rs->fetch_row()) {
                    $v = $row[0];
                    $d = $row[1];
                    if($d=="")
                        $d=$v;
                    $e .= "<option value='".$d."' data-value='".$v."'></option>";
                }
                $rs->close();
            }

            $e.='</datalist>
            <script type="text/javascript">
                function search_value_'.$this->id.'()
                {
                    //take from the textbox
                    var found=false;
                    var v = $("#'.$this->id.'_textbox").val();
                    v = v.toLowerCase();
                    
                    // search for value in list 
                    $("#'.$this->id.'_datalist option").each(function(i,el) {  
                        var tmp = $(el).val();
                        tmp = tmp.toLowerCase();
                        if(tmp == v)
                        {
                            $("#'.$this->id.'_hidden").val($(el).attr("data-value"));
                            found = true;
                            return false;
                        }
                    });
                    
                    if(found==false)
                    {
                        $("#'.$this->id.'_hidden").val($("#'.$this->id.'_textbox").val());
                    }
                }
                function search_text_'.$this->id.'()
                {
                    //take from the hidden
                    var found = false;
                    var v = $("#'.$this->id.'_hidden").val();
                    
                    // search for value in list 
                    $("#'.$this->id.'_datalist option").each(function(i,el) {  
                        var tmp = $(el).attr("data-value");
                        if(tmp == v)
                        {
                            $("#'.$this->id.'_textbox").val($(el).val());
                            found = true;
                            return false;
                        }
                    });
                    
                    if(found==false)
                    {
                        $("#'.$this->id.'_textbox").val($("#'.$this->id.'_hidden").val());
                    }
                }
                search_text_'.$this->id.'();
                
                $("#'.$this->id.'_textbox").blur(function() {
                    search_value_'.$this->id.'();
                });
                $("#'.$this->id.'_textbox").on("input", function () {
                    search_value_'.$this->id.'();
                    var val = this.value;
                });
                /*
                $("#'.$this->id.'_datalist option").click(function() {
                    search_value_'.$this->id.'();
                });
                */
            </script>
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Standardtext",'standardtext');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("SQL string which samples the data from a table. The first column is the one which is stored, usually a foreign key, the second is the column that is displayed (Variables #INDEX1#, #INDEX2#, #KENNZEICHEN1#, #CURRENTVALUE#)",'standardsqlstring');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Max. length",'maxlength');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter();
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