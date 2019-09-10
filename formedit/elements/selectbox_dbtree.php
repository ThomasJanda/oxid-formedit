<?php

class selectbox_dbtree extends basecontrol
{
    var $name="selectbox_dbtree";

    var $editorname="Selectbox Tree";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Selectbox with database connection like a normal selectbox, but can display items as a tree.';


    protected function treezweig($selected="", $parentid="", $level=0, $deact=false)
    {
        $sqlstring=$this->property["standardsqlstring"];
        
        $hsconfig=getHsConfig();
        $index1=$hsconfig->getIndex1Value();
        
        $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
        $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
        $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
        $sqlstring=$hsconfig->parseSQLString($sqlstring,$selected);
        $rs = $hsconfig->execute($sqlstring);
        //$rs=mysql_query($sqlstring,$hsconfig->getDbId());
        
        $html="";
        if($rs)
        {
            while($row = $rs->fetch_array(MYSQLI_NUM))
            {
                if($row[1]==$parentid && $row[0]!="")
                {
                    $html.='<option value="'.$row[0].'" ';
                    if($selected==$row[0])
                    {
                        $html.=" selected='selected' ";
                    }
                    if($deact==true || $index1==$row[0])
                    {
                        $deact=true;
                        $html.=" disabled='disabled' ";
                    }
                    $html.='>'.str_repeat('-',$level).$row[2].'</option>';
                    
                    $tmpparentid=$row[0];
                    $html.=$this->treezweig($selected,$tmpparentid,$level+1,$deact);
                    
                    if($selected==$row[0] || $index1==$row[0])
                    {
                        $deact=false;
                    }
                }
            }
            $hsconfig->close($rs);
        }
        unset($rs);
        
        return $html;
    }

    public function getInterpreterRender()
    {
        $value=parent::getInterpreterRequestValue();
        
		$hsconfig=getHsConfig();
		
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <select 
            data-customerid="'.$this->getCustomerId().'"
                name="'.$this->id.'" 
                style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').'"
                tabindex="'.$this->property['taborder'].'"
            ><option value=""></option>
            ';
            
        $e.=$this->treezweig($value);
            
        $e.='</select></div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textarea("SQL statment to display the tree data. The 
        first column have to be the index of the table (index1, cpid), the second
        column have to be the parent key (parentid, cpparentid) and the third column
        will display in the selectbox. You can use: #INDEX1#, #INDEX2#, #KENNZEICHEN1#)",'standardsqlstring');
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Error message",'fehlermeldung','is required');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
}

?>