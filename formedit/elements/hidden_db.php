<?php

class hidden_db extends basecontrol
{
    var $name="hidden_db";

    var $editorname="Hidden DB";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Invisible input field that can insert default values ​​in the database. For this purpose, a database query to run.';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
    
        $hsconfig=getHsConfig();
        $value="";

        if(parent::getInterpreterIsFirstNew() || parent::getInterpreterIsNew())
        {
            $sqlstring = $this->property['sqlstatment'];
            $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
            $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
            $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
            $sqlstring=$hsconfig->parseSQLString($sqlstring);
            if(trim($sqlstring)!="")
                $value = $hsconfig->getScalar($sqlstring);
            //echo $sqlstring;
            //$rs=mysql_query($sqlstring,$hsconfig->getDbId());
            //if($rs)
            //{
            //    $value=@mysql_result($rs,0,0);
            //}
        }
        else
        {
            $sqlstring = $this->property['sqlstatmentedit'];
            $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
            $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
            $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
            $sqlstring=$hsconfig->parseSQLString($sqlstring);
//            \core\utilities\log::debug($sqlstring);
            if(trim($sqlstring)!="")
                $value = $hsconfig->getScalar($sqlstring);
            //$rs=mysql_query($sqlstring,$hsconfig->getDbId());
            //if($rs)
            //{
            //    $value=@mysql_result($rs,0,0);
            //}
        }
        $e = '<div data-customeridbox="'.$this->getCustomerId().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.$this->property['css'].'">';
        $e.= '<input 
        data-customerid="'.$this->getCustomerId().'" 
        type="hidden" 
        name="'.$this->id.'" 
        value="'.$value.'"
        >';
        if($this->property['debugmode']=="1") $e.=$this->property['bezeichnung']."=".$value."<br>".$sqlstring;
        $e.= '</div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox('Title','bezeichnung');
        
        $html.=parent::getEditorProperty_Textarea("If the from gets load the first time. SQL-Statment to get the value. Variablen: #INDEX1#, #INDEX2#, #KENNZEICHEN1#",'sqlstatment');
        $html.=parent::getEditorProperty_Textarea("If the from gets load a further time. SQL-Statment to get the value. Variablen: #INDEX1#, #INDEX2#, #KENNZEICHEN1#",'sqlstatmentedit');

        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,true,false,false);
        return $html;
    }
}

?>