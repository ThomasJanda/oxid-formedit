<?php

class checkbox_unique extends basecontrol
{
    var $name="checkbox_unique";

    var $editorname="Checkbox Unique";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Simple checkbox, which deletes all the values ​​that are stored in the same column before. Thus there is always more than one record only a single "true" in the column. Useful for selecting a default settings between different data sets.';


    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $s=parent::interpreterSaveNew($table, $colindex, $indexvalue);
        if($s!==false && $this->getInterpreterRequestValue()=="1")
        {
            $col=$this->property['datenbankspalte'];
            $sqlstring="update $table set $col=0 where $col=1";
            $hsconfig=getHsConfig();
            $hsconfig->executeNoReturn($sqlstring);
        }
        return $s;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $s=parent::interpreterSaveEdit($table, $colindex, $indexvalue);
        if($s!==false && $this->getInterpreterRequestValue()=="1")
        {
            $col=$this->property['datenbankspalte'];
            $sqlstring="update $table set $col=0 where $col=1";
            $hsconfig=getHsConfig();
            $hsconfig->executeNoReturn($sqlstring);
            //mysql_query($sqlstring,$hsconfig->getDbId());
        }
        return $s;
    }
    

    public function getInterpreterRender()
    {
        $checked=false;
        if(parent::getInterpreterIsFirstNew())
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
            <input type="hidden" name="'.$this->id.'" value="0">
            <input 
                data-customerid="'.$this->getCustomerId().'" 
                type="checkbox" 
                name="'.$this->id.'" 
                value="1" 
                '.($checked?'checked':'').' 
                style="vertical-align:middle; "
                tabindex="'.$this->property['taborder'].'"
                >
            '.$this->property['bezeichnung'].'
        </div>';
        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Checkbox("Checked",'angehakt');
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
	function getSQL($table)
	{
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` TINYINT(1) DEFAULT 0; ";
	}
}

?>