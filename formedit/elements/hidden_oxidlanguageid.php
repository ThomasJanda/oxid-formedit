<?php

class hidden_oxidlanguageid extends basecontrol
{
    var $name="hidden_oxidlanguageid";

    var $editorname="Hidden Oxid Languageid";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Save the current language id (0, 1...) into a column. Mostly oxlang.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();

        $e = '<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$hsconfig->getLangId().'">';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,true,false,false,true,false,false);
        return $html;
    }
    function getLanguageArray($add=array())
    {
        return false;
    }

    function getSQL($table)
    {
        $dbfield=$this->property['datenbankspalte'];
        $dbfield=str_replace('#EDITLANG#','',$dbfield);

        if(trim($dbfield)=="" || $table=="")
            return "";
        $sqlstring = "alter table `".$table."` add column `".$dbfield."` int(2) NOT NULL DEFAULT '0' ";

        $dbfielddescription = trim($this->property['datenbankspaltebeschreibung']);
        if($dbfielddescription!="")
        {
            $db = getHsConfig()->getDbId();
            $escapedDbFieldDescription = getHsConfig()->escapeString($dbfielddescription);
            $sqlstring.=" COMMENT '$escapedDbFieldDescription'";
        }
        $sqlstring.=";";

        return $sqlstring;
    }
}

?>