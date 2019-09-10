<?php

class datebox2 extends basecontrol
{
    var $name="datebox2";

    var $editorname="Datebox2";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Simple field, which can display a date using a calendar pop-ups. It can be set a limitation of the date by SQL.';

    public function getInterpreterRender()
    {
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
            if($this->property['standard']=="1")
                $value=date('Y-m-d',time());
        }
        else
        {
            $value=parent::getInterpreterRequestValue();
        }
        
        //$value="2013-6-3";
        if(strpos($value," ")!==false)
        {
            $value=explode(" ",$value);
            $value=$value[0];
        }
        
    
        $mindate="";
        if($this->property['hasmindate']=="1")
        {
            $sqlstring=$this->property["mindatesqlstring"];
    		$hsconfig=getHsConfig();
            
            $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
            $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
            $sqlstring=str_replace('#VALUE#',$value,$sqlstring);
            $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
            $sqlstring=$hsconfig->parseSQLString($sqlstring, $value);
            
            /*
            echo '<pre>';
            echo htmlentities($sqlstring);
            echo '</pre>';
            */

            $mindate = $hsconfig->getScalar($sqlstring);
    		//$rs=mysql_query($sqlstring,$hsconfig->getDbId());
            //$mindate=mysql_result($rs,0,0);
            if($mindate=="0" || $mindate=="0000-00-00")
            {
                $mindate="";
            }
        }
        $maxdate="";
        if($this->property['hasmaxdate']=="1")
        {
            $sqlstring=$this->property["maxdatesqlstring"];
    		$hsconfig=getHsConfig();
            
            $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
            $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
            $sqlstring=str_replace('#VALUE#',$value,$sqlstring);
            $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
            $sqlstring=$hsconfig->parseSQLString($sqlstring,$value);

            /*
            echo '<pre>';
            echo htmlentities($sqlstring);
            echo '</pre>';
            */

    		//$rs=mysql_query($sqlstring,$hsconfig->getDbId());
            //$maxdate=mysql_result($rs,0,0);
            $maxdate = $hsconfig->getScalar($sqlstring);
            if($maxdate=="0" || $maxdate=="0000-00-00")
            {
                $maxdate="";
            }
        }
    
        if($this->property['readonly']=="1")
        {
            $e = '
            <input 
            data-customerid="'.$this->getCustomerId().'" 
            type="hidden" 
            name="'.$this->id.'" 
            value="'.($value=="0000-00-00"?"":$value).'
            ">
            <div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['readonly']=="1"?'opacity:0.5;':"").' '.($this->property['invisible']=="1"?' display:none; ':'').'">
                <input id="input'.$this->id.'"  type="text" readonly="readonly" value="'.($value=="0000-00-00"?"":$value).'" readonly="readonly" style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' " tabindex="'.$this->property['taborder'].'">
            </div>';            
        }
        else
        {
            $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
                <input data-customerid="'.$this->getCustomerId().'" id="hidden'.$this->id.'" type="hidden" name="'.$this->id.'" value="'.$value.'">
                <input id="input'.$this->id.'"  type="text"   name="input'.$this->id.'" value="'.($value=="0000-00-00"?"":$value).'" readonly="readonly" style="vertical-align:middle; width:'.($this->width-26).'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' " tabindex="'.$this->property['taborder'].'">
                <span id="span'.$this->id.'" class="ui-icon ui-icon-closethick" style="width:16px; height:'.$this->height.'px; line-height:'.$this->height.'px; cursor:pointer; float:right; ">del.</span>
                <script>
                    $("#input'.$this->id.'").datepicker({
                        beforeShow: function() {
                            setTimeout(function(){
                                $(\'.ui-datepicker\').css(\'z-index\', 99999999999999);
                            }, 0);
                        },
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: "yy-mm-dd",
                        altField: "#hidden'.$this->id.'",
                        altFormat: "yy-mm-dd"
                        ';
                        if($mindate!="")
                            $e.= ', minDate: "'.$mindate.'" ';
                        if($maxdate!="")
                            $e.= ', maxDate: "'.$maxdate.'" ';
                    $e.= ' });
                    $("#span'.$this->id.'").click(function() {
                        $("#input'.$this->id.'").val("");
                        $("#hidden'.$this->id.'").val("");
                    });
                </script>                  
            </div>';
        }
        
        return $e;
    }


    public function getInterpreterRequestValue()
    {
        if(isset($_REQUEST[$this->id])==false)
            return "";
        $tmp = trim(stripslashes($_REQUEST[$this->id]));
        if($tmp=="")
            $tmp="0000-00-00";
        return $tmp;
    }
    public function interpreterProve($table, $colindex, $indexvalue)
    {
        $error="";
        if(isset($this->property['pflichtfeld']) && isset($this->property['fehlermeldung']))
        {
            if($this->property['pflichtfeld']=='1')
            {
                if($this->getInterpreterRequestValue()=="" || $this->getInterpreterRequestValue()=="0000-00-00")
                {
                    $error=$this->property['fehlermeldung'];
                }
            }
            if($error!="")
                return array($this->id => $error);
        }
        return false;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        //$html.=parent::getEditorProperty_Checkbox("Soll das aktuelle Datum angezeigt werden, wenn keine Wert hinterlegt ist?",'standard');

        $html.=parent::getEditorProperty_Checkbox("Should the current date be displayed if no value is stored?",'standard');
        $html.=parent::getEditorProperty_Line();
        
        $html.=parent::getEditorProperty_Checkbox("Has a min date",'hasmindate');
        $html.=parent::getEditorProperty_Textarea("SQL that recived a min date. The returnvalue must be a date (YYYY-MM-DD) or 0 (Variables #INDEX1#,#INDEX2#,#KENNZEICHEN1#,#VALUE#)",'mindatesqlstring');
        $html.=parent::getEditorProperty_Line();
        
        $html.=parent::getEditorProperty_Checkbox("Has a max date",'hasmaxdate');
        $html.=parent::getEditorProperty_Textarea("SQL that recived a max date. The returnvalue must be a date (YYYY-MM-DD) or 0 (Variablen #INDEX1#,#INDEX2#,#KENNZEICHEN1#,#VALUE#)",'maxdatesqlstring');
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
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` DATE default '0000-00-00'; ";
	}
}

?>