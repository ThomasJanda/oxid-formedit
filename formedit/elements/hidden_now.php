<?php

class hidden_now extends basecontrol
{
    var $name="hidden_now";

    var $editorname="Hidden now";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible input field, which can have a NOW () put in a new column when creating a record. Then this value is always maintained. Suitable for timestamp fields.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
            $value="";
        }
        else
        {
            $value=parent::getInterpreterRequestValue();
        }
        
        $e = '<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$value.'">';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,true,false,false);
        return $html;
    }
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        if($s=parent::interpreterSaveNew($table, $colindex, $indexvalue))
        {
            $s['value']="NOW()";
        }
        return $s;
    }
	function getSQL($table)
	{
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` DATETIME default '0000-00-00 00:00:00'; ";
	}
}

?>