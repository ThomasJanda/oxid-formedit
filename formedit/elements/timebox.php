<?php

class timebox extends basecontrol
{
    var $name="timebox";

    var $editorname="Timebox";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Simple field that can display a clock within a calendar popup';

    public function getInterpreterRender()
    {
        $value="";
        if(parent::getInterpreterIsFirstNew())
        {
            if($this->property['standard']=="1")
                $value="12:00:00";
        }
        else
        {
            $value=parent::getInterpreterRequestValue();
        }
    
    
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                data-customerid="'.$this->getCustomerId().'"
                id="input'.$this->id.'" 
                type="textbox" 
                readonly="readonly" 
                name="'.$this->id.'" 
                value="'.$value.'" 
                style="vertical-align:middle; width:'.($this->width-26).'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' "
                tabindex="'.$this->property['taborder'].'"
            >
            <span id="span'.$this->id.'" class="ui-icon ui-icon-closethick" style="width:16px; height:'.$this->height.'px; line-height:'.$this->height.'px; cursor:pointer; float:right; ">del.</span>
            <script>
            	$(function() {
            		$( "#input'.$this->id.'" ).AnyTime_picker({ 
                        format: "%H:%i:%s", 
                        labelTitle: "Time",
                        labelHour: "Hour", 
                        labelMinute: "Minute",
                        labelSecond: "Second" 
                    });
                    $( "#span'.$this->id.'" ).click(function() { $( "#input'.$this->id.'" ).val(""); });
            	});
        	</script>
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Checkbox("Should the current date will be displayed if no value is stored?",'standard');
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','Is required');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }

	function getSQL($table)
	{
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` TIME; ";
	}
}

?>