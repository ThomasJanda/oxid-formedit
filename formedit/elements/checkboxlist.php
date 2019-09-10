<?php
class checkboxlist extends basecontrol
{
    var $name="checkboxlist";

    var $editorname="Checkboxlist";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='List with Checkboxes that save the result into a other table (1:n). Usefull if the user must select more than one option and it can save in a simple data structure.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();

        $values=array();
        $new=false;
        $sqlstring2="";
        if(parent::getInterpreterIsFirstNew())
        {
            $new=true;
        }
        elseif(parent::getInterpreterIsFirstEdit())
        {
            //echo "1";
            $values=array();
            $savetable=$this->property['savetable'];
            $savetableindex=$this->property['savetableindex'];
            $savetableforeign1=$this->property['savetableforeign1'];
            $savetableforeign2=$this->property['savetableforeign2'];
            
    		if(trim($savetableforeign2)!="" && trim($savetableforeign1)!="" && trim($savetableindex)!="" && trim($savetable)!="")
            {
                $index1=$hsconfig->getIndex1Value();
                $sqlstring2="select `".$savetableforeign2."` from `".$savetable."` where `".$savetableforeign1."`='".$hsconfig->escapeString($index1)."'";
                $rs = $hsconfig->execute($sqlstring2);
                if($rs)
                {
                    while($row = $rs->fetch_array(MYSQLI_NUM))
                    {
                        $values[]=$row[0];
                    }
                    $hsconfig->close($rs);
                }

                /*
                $rs=mysql_query($sqlstring2,$hsconfig->getDbId());
                if($rs)
                {
                    for($x=0;$x<mysql_num_rows($rs);$x++)
                    {
                        $values[]=mysql_result($rs,$x,0);
                    }
                }
                */
            }
        }
        else
        {
            $values=parent::getInterpreterRequestValues(); 
        }
            
        $sqlstring=$this->property["sqldisplay"];
        $sqlstring=$hsconfig->parseSQLString($sqlstring,"");
        $rs = $hsconfig->execute($sqlstring);
		//$rs=mysql_query($sqlstring,$hsconfig->getDbId());
            
        $e="";        
        $e.= '<div tabindex="'.$this->property['taborder'].'" data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; overflow:hidden; overflow-y:auto; '.($this->property['invisible']=="1"?' display:none; ':'').'">';
        if($this->property['debugmode']=="1")
        {
            $e.='<div style="line-height:20px; "><b>Select:</b><br>'.htmlentities($sqlstring).'</div>';
            $e.='<div style="line-height:20px; "><b>Values:</b><br>'.htmlentities($sqlstring2).'</div>';
        }
        if($rs)
        {
            $e.="<div style='line-height:20px; '>
                <button type='button' onclick='checkboxlist".$this->id."_select(); '>Select all</button>
                <button type='button' onclick='checkboxlist".$this->id."_unselect(); '>Unselect all</button>
            </div>";

            while($row = $rs->fetch_array(MYSQLI_NUM))
            {
                $checked=false;
                if($new==true)
                {
                    $checked=($row[2]=='1'?true:false);
                }
                else
                {
                    if(is_array($values) && count($values)>0 && in_array($row[0],$values))
                    {
                        $checked=true;
                    }
                }
                $e.="<div style='line-height:20px; '>
                    <input name='".$this->id."[]' id='".$interpreterid.$this->name.$this->id.$x."' type='checkbox' value='".$row[0]."'  ".($checked?'checked':'')."><label for='".$interpreterid.$this->name.$this->id.$x."'>".$row[1]."</label>
                </div>";
            }
        }
        $e.='</div>
        <script type="text/javascript"> 
            $("#'.$this->id.' button").button(); 
            
            function checkboxlist'.$this->id.'_select()
            {
                $("#'.$this->id.' input").attr("checked", true);
            }
            function checkboxlist'.$this->id.'_unselect()
            {
                $("#'.$this->id.' input").attr("checked", false);
            }
        </script>
        ';
        return $e;
    }
    
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $savetable=$this->property['savetable'];
        $savetableindex=$this->property['savetableindex'];
        $savetableforeign1=$this->property['savetableforeign1'];
        $savetableforeign2=$this->property['savetableforeign2'];
        
		if(trim($savetableforeign2)=="" || trim($savetableforeign1)=="" || trim($savetableindex)=="" || trim($savetable)=="")
			return false;        
        
        $hsconfig=getHsConfig();
        $values=parent::getInterpreterRequestValues();
        
        $sqlstring="delete from `".$savetable."` where `".$savetableforeign1."`='".$hsconfig->escapeString($indexvalue)."'";
        $hsconfig->executeNoReturn($sqlstring);
        //mysql_query($sqlstring,$hsconfig->getDbId());
        foreach($values as $value)
        {
            $sqlstring="insert into `".$savetable."` (
            `".$savetableindex."`,
            `".$savetableforeign1."`,
            `".$savetableforeign2."`
            ) values (
            '".$hsconfig->escapeString(uniqid(""))."',
            '".$hsconfig->escapeString($indexvalue)."',
            '".$hsconfig->escapeString($value)."'
            )";
            $hsconfig->executeNoReturn($sqlstring);
            //mysql_query($sqlstring,$hsconfig->getDbId());
        }
        return false;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $savetable=$this->property['savetable'];
        $savetableindex=$this->property['savetableindex'];
        $savetableforeign1=$this->property['savetableforeign1'];
        $savetableforeign2=$this->property['savetableforeign2'];
        
		if(trim($savetableforeign2)=="" || trim($savetableforeign1)=="" || trim($savetableindex)=="" || trim($savetable)=="")
			return false;        
        
        $hsconfig=getHsConfig();
        $values=parent::getInterpreterRequestValues();
        
        $sqlstring="delete from `".$savetable."` where `".$savetableforeign1."`='".$hsconfig->escapeString($indexvalue)."'";
        //mysql_query($sqlstring,$hsconfig->getDbId());
        $hsconfig->executeNoReturn($sqlstring);
        foreach($values as $value)
        {
            $sqlstring="insert into `".$savetable."` (
            `".$savetableindex."`,
            `".$savetableforeign1."`,
            `".$savetableforeign2."`
            ) values (
            '".$hsconfig->escapeString(uniqid(""))."',
            '".$hsconfig->escapeString($indexvalue)."',
            '".$hsconfig->escapeString($value)."'
            )";
            //echo $sqlstring."<br>";
            //mysql_query($sqlstring,$hsconfig->getDbId());
            $hsconfig->executeNoReturn($sqlstring);
        }
        return false;
    }



    public function interpreterProveNew($table, $colindex, $indexvalue)
    {
        return $this->interpreterProve($table, $colindex, $indexvalue);
    }
    public function interpreterProveEdit($table, $colindex, $indexvalue)
    {
        return $this->interpreterProve($table, $colindex, $indexvalue);
    }
    public function interpreterProve($table, $colindex, $indexvalue)
    {
        if($this->property['pflichtfeld']=="1")
        {
            $hsconfig=getHsConfig();
            $values=parent::getInterpreterRequestValues();
            if(!is_array($values) || count($values)==0)
            {
                $error=$this->property['fehlermeldung'];
                return array($this->id => $error);
            }
        }
        return false;
    }



    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textarea("SQL-statment for the checkboxlist (first column must be a index, second column gets displayed, third column is 0 (default) or 1 if the value is checked at the first load.)",'sqldisplay');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Table where the values should save",'savetable');
        $html.=parent::getEditorProperty_Textbox("Index column from the save table",'savetableindex','index1');
        $html.=parent::getEditorProperty_Textbox("Foreign key column from the save table to the table of this formular",'savetableforeign1');
        $html.=parent::getEditorProperty_Textbox("Foreign key column from the sqlstatment above",'savetableforeign2');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true, false, true, true);
        return $html;
    }

	function getSQL($table)
	{
        $savetable=$this->property['savetable'];
        $savetableindex=$this->property['savetableindex'];
        $savetableforeign1=$this->property['savetableforeign1'];
        $savetableforeign2=$this->property['savetableforeign2'];
        
		if(trim($savetableforeign2)=="" || trim($savetableforeign1)=="" || trim($savetableindex)=="" || trim($savetable)=="")
			return "";
            
		return "CREATE TABLE `$savetable` (
`$savetableindex` varchar(50) NOT NULL,
`$savetableforeign1` varchar(50) NOT NULL,
`$savetableforeign2` varchar(50) NOT NULL,
UNIQUE KEY `$savetableindex` (`$savetableindex`),
KEY `$savetableforeign1` (`$savetableforeign1`),
KEY `$savetableforeign2` (`$savetableforeign2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}
}
