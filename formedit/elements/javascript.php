<?php

class javascript extends basecontrol
{
    var $name="javascript";

    var $editorname="Javascript";
    var $editorcategorie="Script Elements";
    var $editorshow=true;
    var $editordescription='Javascript where you can react on form changes in jquery like $("#SELECTBOX").change(function() { ... }); ';


    public function getInterpreterRender()
    {
        return "";
    }
    public function interpreterFinishJavascript()
    {
        $sJs = "";

        if($this->property['javascriptfile']!="")
        {
            $hsConfig = getHsConfig();
            $projectBaseDir = $hsConfig->getProjectBaseDir();

            $p = $projectBaseDir."/scriptjs/".$this->property['javascriptfile'];
            $p = str_replace("//","/",$p);
            if (file_exists($p))
                $sJs.=file_get_contents($p);
        }

        $sJs.= "\n\r".$this->property['javascript']."\n\r";

        return $sJs;
    }
    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Textarea("Description",'beschreibung');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Pure javascript in a file (Folder: scriptjs) (Example: myscript.js)","javascriptfile");
        $html.=parent::getEditorProperty_Textarea("Pure javascript","javascript");
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }
}

?>