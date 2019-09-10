<?php

class htmlview extends basecontrol
{
    var $name="htmlview";

    var $editorname="HTML-View";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Shows a html-code from the database';

    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        return false;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        return false; 
    }
    
    public function getInterpreterRender()
    {
        $value=parent::getInterpreterRequestValue();
    
        //save in tmp folder and show the result in a iframe
        $hsconfig=getHsConfig();
        $otab=$hsconfig->getTab();
        $path=$hsconfig->getBaseDir()."/tmp/".$otab->getTableName()."-".$hsconfig->getIndex1Value().".html";
        $url=$hsconfig->getBaseUrl()."/tmp/".$otab->getTableName()."-".$hsconfig->getIndex1Value().".html";
    
        file_put_contents($path,$value);
        
        $e = '<div data-customerid="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().'overflow:hidden; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">';
        
            if(file_exists($path) && $value!="")
            $e.='
            <iframe 
                data-customerid="'.$this->getCustomerId().'" 
                src="'.$url.'?uid='.uniqid("").'"
                scrolling="yes"
                style="border:1px solid #cccccc; 
                width:'.$this->width.'px; 
                height:'.$this->height.'px; "
                tabindex="'.$this->property['taborder'].'"
            ></iframe>';
            
        $e.='</div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,true,false);
        return $html;
    }
 

}

?>