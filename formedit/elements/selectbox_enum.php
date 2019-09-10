<?php

class selectbox_enum extends basecontrol
{
    var $name="selectbox_enum";

    var $editorname="Selectbox ENUM";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription="Selectbox that display 'enum' values from the database";

    public function getInterpreterRender()
    {
        $value=parent::getInterpreterRequestValue();

        $e="";
        if($this->property['readonly']=="1")
        {
            $e.='<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$value.'">';
        }

        $e.= '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <div id="ajaxcontent'.$this->id.'">
            '.$this->getSelectbox().'
            </div>
        </div>';
        return $e;
    }
    public function getInterpreterRenderAjax()
    {
        return $this->getSelectbox();
    }
    public function getSelectbox()
    {
        $hsconfig=getHsConfig();
        /** @var mysqli $db */
        $db = $hsconfig->getDbId();

        if(parent::getInterpreterIsFirstNew())
            $value=$this->property['default'];
        else
            $value=parent::getInterpreterRequestValue();

        $sqlstring="SELECT column_type
        FROM information_schema.`COLUMNS`
        WHERE TABLE_NAME = '".$this->property['tablename']."'
        AND COLUMN_NAME = '".$this->property['columnname']."'
        AND table_schema = '".$hsconfig->getDbName()."'";
        $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
        $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
        $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
        $sqlstring=$hsconfig->parseSQLString($sqlstring,$value);
        // new feature, replace /*EXTRA()EXTRA*/ tags
        $sqlstring = $this->replaceExtraSql($sqlstring);

        $e="";
        if($this->property['debugmode']=="1")
        {
            $e.='<div><b>Select:</b><br>'.htmlentities($sqlstring).'</div>';
        }
        $e.='<select ';
        if($this->property['readonly']=="1")
        {
            $e.='data-customerid="'.$this->getCustomerId().'" ';
        }
        $e.='
                name="'.$this->id.'" 
                style="vertical-align:middle; width:'.($this->width - 20).'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' '.($this->property['readonly']=="1"?'opacity:0.5;':"").' "
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'readonly="readonly" disabled="disabled"':"").'
            >
            ';

        $values = "";
        if ($rs = $db->query($sqlstring)) {
            $values = $rs->fetch_row()[0];
            $rs->close();
        }

        if($values!="")
        {
            $values = trim($values);
            $values = substr($values,strpos($values,"('"));
            $values = substr($values,2,strlen($values)-4);
            $values = explode("','",$values);

            $aDeactivateOption = [];
            if(trim($this->property['deactivateoption'])!="")
            {
                $aDeactivateOption=explode("|",$this->property['deactivateoption']);
                $aDeactivateOption=array_map('trim',$aDeactivateOption);
            }

            foreach($values as $valuedb)
            {
                $selected = $value == $valuedb ? "selected" : "";
                $deactivated = "";
                if($selected=="")
                {
                    if(in_array($valuedb,$aDeactivateOption))
                    {
                        $deactivated='disabled';
                    }
                }
                $e .= "<option value='$valuedb' $selected $deactivated>$valuedb</option>";
            }
        }

        $e.='</select>';

        return $e;

    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Tablename",'tablename','');
        $html.=parent::getEditorProperty_Textbox("Column",'columnname','');
        $html.=parent::getEditorProperty_Textbox("Default value",'default','');
        $html.=parent::getEditorProperty_Textbox("Deactivate option if not active (separate with |)",'deactivateoption','');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }


    function getSQL($table)
    {
        $dbfield=$this->property['datenbankspalte'];
        if(trim($dbfield)=="" || $table=="")
            return "";
        return "alter table `".$table."` add column `".$dbfield."` enum('".$this->property['default']."','OTHER VALUES') NOT NULL DEFAULT '".$this->property['default']."'; ";
    }


}

?>
