<?php

class datetimebox extends basecontrol
{
    var $name="datetimebox";

    var $editorname="Datetimebox";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Simple field, which can display a date and time using a calendar pop-ups';

    public function getInterpreterRender()
    {
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
            if($this->property['standard']=="1")
                $value=date('Y-m-d',time())." 12:00:00";
        }
        else
        {
            $value=parent::getInterpreterRequestValue();
        }
    
        if($value=="0000-00-00 00:00:00")
            $value="";

        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                data-customerid="'.$this->getCustomerId().'" 
                id="input'.$this->id.'" 
                type="textbox" 
                readonly="readonly" 
                name="'.$this->id.'" 
                value="'.$value.'" 
                style="vertical-align:middle; width:'.($this->width-26).'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' '.($this->property['readonly']=="1"?'opacity:0.5;':"").' "
                tabindex="'.$this->property['taborder'].'"
            >';
            if($this->property['readonly']!="1")
            {
                $e.='<span id="span'.$this->id.'" class="ui-icon ui-icon-closethick" style="width:16px; height:'.$this->height.'px; line-height:'.$this->height.'px; cursor:pointer; float:right; ">del.</span>
                <script>
                	$(function() {
                		$( "#input'.$this->id.'" ).AnyTime_picker({ 
                            format: "%Y-%m-%d %H:%i:%s", 
                            labelTitle: "Datetime",
                            labelHour: "Hour", 
                            labelMinute: "Minute",
                            labelSecond: "Second" 
                        });
                        $( "#span'.$this->id.'" ).click(function() { $( "#input'.$this->id.'" ).val(""); });
                	});
            	</script>';
            }

        $e.='</div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Checkbox("Should the current date be displayed if no value is stored?",'standard');
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
		return "alter table `".$table."` add column `".$dbfield."` DATETIME default '0000-00-00 00:00:00'; ";
	}
}

?>