<?php

class textarea extends basecontrol
{
    var $name="textarea";

    var $editorname="Textarea";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='HTML textarea';

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

            /*
            if(parent::getInterpreterIsFirstEdit() && $this->property['compress'])
            {
                //was load from the db
                if($value!="")
                    $value = gzuncompress(base64_decode($value));
            }
            */
        }

        $maxlength = $this->property['maxlength'];
        if (is_numeric($maxlength) && $maxlength > 0) {
            $maxlength = ' maxlength="' . $maxlength . '" ';
        }

        $csswidth = "width:".$this->width."px;";
        if($this->property['fixwidth']=="0")
        {
            $csswidth = "width:calc(100% - ".($this->left * 2)."px);";
        }

        $e = '<div 
            data-customeridbox="'.$this->getCustomerId().'" 
            data-hasparentcontrol="'.$this->getParentControl().'" 
            class="'.$this->property['classname'].'" id="'.$this->id.'" 
            style="'.$this->getParentControlCss().' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; '.$csswidth.' height:'.$this->height.'px; '.($this->property['css'] ?: $this->property['style']).' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <textarea 
                data-customerid="'.$this->getCustomerId().'"
                '.($this->property['readonly']==1?'readonly="readonly"':'').'
                name="'.$this->id.'" 
                ' . $maxlength . '
                style="width:100%; height:100%;border:1px solid #dddddd;box-sizing: border-box; resize: none ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'border-color:red; ' : '') . '"
                tabindex="'.$this->property['taborder'].'"
            >'.$value.'</textarea>
        </div>';
        return $e;
    }

    /*
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
            $s = parent::interpreterSaveNew($table, $colindex, $indexvalue);
        return $s;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $s=false;
            $s = parent::interpreterSaveEdit($table, $colindex, $indexvalue);
        return $s;
    }
    */

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Standardtext",'standardtext');
        $html.=parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Required", 'pflichtfeld');
        $html .= parent::getEditorProperty_Textbox("Errormessage", 'fehlermeldung', 'Is mandatory');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textbox("Max. length", 'maxlength');
        $html .= parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }

	function getSQL($table)
	{
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` TEXT; ";
	}
}

?>
