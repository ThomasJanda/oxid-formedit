<?php

class link extends basecontrol
{
    var $name="link";

    var $editorname="Link";
    var $editorcategorie="Navigation";
    var $editorshow=true;
    var $editordescription='Link, which referenced another form from the project. The current index-value of the form will attach.';


    public function getInterpreterRender()
    {
        $hsconfig = getHsConfig();
        if ($this->property['readonly'] == "1") {
            $e = "";
        } else {
            if ($projectBaseDir = $hsconfig->getProjectBaseDir()) {
                $project = "projectload=" . $this->property['project'];
            } else {
                $project = 'projectload=' . $this->property['project'];
            }
            $url = $hsconfig->getBaseUrl() . '/interpreter.php?' . $project . '&index2value=' . $hsconfig->getIndex1Value() . '&uid=' . uniqid("") . (trim($this->property['urlparameter']) != "" ? '&' . $hsconfig->parseSQLString($this->property['urlparameter']) : '');

            $e = '<div
            data-customeridbox="' . $this->getCustomerId() . '"
            data-hasparentcontrol="' . $this->getParentControl() . '"
            class="' . $this->property['classname'] . '"
            id="' . $this->id . '"
            style="' . $this->getParentControlCss() . '' . $this->property['css'] . ' ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'background-color:red; ' : '') . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; ' . ($this->property['invisible'] == "1" ? ' display:none; ' : '') . '"
            >
                <a data-customerid="' . $this->getCustomerId() . '"
                href="' . $url . '"
                target="' . $this->property['target'] . '">
                ' . $this->property['bezeichnung'] . '
                </a>
            </div>';
        }
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
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Line();
        //$html.=parent::getEditorProperty_SelectboxFiles("Project",'project');
        $html.=parent::getEditorProperty_Textbox("Projectpath relative from this project",'project');
        $html.=parent::getEditorProperty_Textbox("Url-Parameter (seperate by &, you can use #INDEX1#,#INDEX2#. Example of parameter you can use: navi=EDIT|NEW, formularid=id of a formular, index1value if you need to load a row.)",'urlparameter');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Selectbox("Target",'target',array(''=>'Standard','_blank'=>'_blank','_self'=>'_self','_parent'=>'_parent','_top'=>'_top'),'');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Disabled",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,true);
        return $html;
    }

}

?>
