<?php

class textbox_numeric extends basecontrol
{
    var $name="textbox_numeric";

    var $editorname="Textbox numerically";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='e.g. zip, customerids';


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
        }
    
        $maxlength=$this->property['maxlength'];
        if(is_numeric($maxlength) && $maxlength>0)
        {
            $maxlength=' maxlength="'.$maxlength.'" ';
        }
        
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                data-customerid="'.$this->getCustomerId().'"
                id="textbox'.$this->id.'" 
                type="textbox" 
                name="'.$this->id.'" 
                value="'.$value.'" 
                '.$maxlength.'
                style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' "
                tabindex="'.$this->property['taborder'].'"
            >
        </div>
        <script type="text/javascript">
            $(\'#textbox'.$this->id.'\').keypress(function(e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            }).blur(function() {
                var content = $(this).val();
                var number = content.replace(/\D+/g,"");
                $(this).val(number);
            });
            
        </script>
        ';
        return $e;
    }
    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Standardtext",'standardtext');
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','Is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Max. length",'maxlength');
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
}

?>