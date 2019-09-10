<?php

class session_index_view extends basecontrol
{
    var $name="session_index_view";

    var $editorname="Session Index View";
    var $editorcategorie="Interform";
    var $editorshow=true;
    var $editordescription='Reads a value from the session<br>
    You can use the value in all querys.<br>
    #SESSION.FORMCUSTOMERID.ELEMENTCUSTOMERID#<br>
    FORMCUSTOMERID=Customer ID of the form<br>
    ELEMENTCUSTOMERID=Customer ID of the element<br>
    ';

    public function getInterpreterRender()
    {
        $e="";
        $hsconfig=getHsConfig();
        
        $wert=$_SESSION["interpreter"]['interfromulardata'][$this->property['formularcustomerid'].".".$this->property['elementcustomerid']];
        $var="#SESSION.".$this->property['formularcustomerid'].".".$this->property['elementcustomerid']."#";
        
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
            '.$var.'<br>
            '.$wert.'
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox('Title','bezeichnung');
        $html.=parent::getEditorProperty_Textbox('Form Customer ID','formularcustomerid');
        $html.=parent::getEditorProperty_Textbox('Element Customer ID','elementcustomerid');
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        
        return $html;
    }
}

?>