<?php
require_once __DIR__."/label_db.php";
class label_db2 extends label_db
{
    var $name="label_db2";

    var $editorname="Label DB2";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Simple label field. Load a value from the current table.';



    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
        $title=parent::getInterpreterRequestValue();

        if($this->property['format']=="currency")
            $title=$this->currency_format($title, ".", ",", 2);

        $property = $this->property;
        $e = "<div data-customeridbox='{$this->getCustomerId()}' data-hasparentcontrol='{$this->getParentControl()}'
                class='$property[classname]' id='$this->id' style='
                {$this->getParentControlCss()}
                position:absolute;
                box-sizing:border-box; 
                line-height:{$this->height}px; 
                vertical-align:middle; 
                left:{$this->left}px;
                top:{$this->top}px; 
                width:{$this->width}px;
                height:{$this->height}px; 
                $property[style]".
                ($property["invisible"]=='1'?" display:none; ":"").
            "'>$title</div>";
        return $e;
    }

    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        return false;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        return false;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Selectbox("Format",'format',array(''=>'auto','currency'=>'Currency'),'');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,true,false);
        return $html;
    }

}

?>