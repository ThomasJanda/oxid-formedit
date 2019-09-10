<?php

class errorhandler3 extends basecontrol
{
    var $name="errorhandler3";

    var $editorname="Errorhandler 3";
    var $editorcategorie="Error";
    var $editorshow=true;
    var $editordescription='Error handler will pass a values ​​of the elements to the DB can and can check by SQL.';

    public function getInterpreterRender()
    {
        $e="";
        return $e;
    }

    public function getEditorRender($text="")
    {
        $e=parent::getEditorRender($text);
        $e = '<div class="element" id="'.$this->id.'" style="background-color:red; color:white; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
            '.$this->property['bezeichnung'].' - 
            <input type="hidden" name="classname" value="'.get_class($this).'">
            <input type="hidden" name="containerid" value="'.$this->containerid.'">
            &nbsp;'.($text!=""?$text." (":"").$this->editorcategorie.' - '.$this->editorname.($text!=""?")":"").'
        </div>';
        return $e;
    }
    
    public function interpreterProveNew($table, $colindex, $indexvalue)
    {
        return $this->interpreterProve($table, $colindex, $indexvalue);
    }
    public function interpreterProveEdit($table, $colindex, $indexvalue)
    {
        return $this->interpreterProve($table, $colindex, $indexvalue);
    }
    public function interpreterProve($table, $colindex, $indexvalue)
    {
        //echo "1234";
        $error="";
        if(isset($this->property['sqlprove']) && isset($this->property['errortext']))
        {
            $hsconfig=getHsConfig();
            $sqlstring = $this->property['sqlprove'];
            
            $oelements=$this->interpreterGetElements();
            if($oelements!=null)
            {
                //echo "123";
                foreach($oelements as $oe)
                {
                    if($oe->getCustomerId()!="")
                    {
                        $sqlstring=str_replace("#ELEMENT.".$oe->getCustomerId()."#",trim($oe->getInterpreterRequestValue()),$sqlstring);
                    }
                }
            }
            
            $sqlstring=str_replace('#INDEX1#',$indexvalue,$sqlstring);
            $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
            $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
            $sqlstring=$hsconfig->parseSQLString($sqlstring);
            if($this->property['debugmode']=="1")
            {
                echo '<div><b>Prove:</b><br>'.htmlentities($sqlstring).'</div>';
            }

            /*
            $rs=mysql_query($sqlstring,$hsconfig->getDbId());
            $pv="";
            if($rs && mysql_num_rows($rs)>0)
                $pv=trim(mysql_result($rs,0,0));
            */
            $pv = $hsconfig->getScalar($sqlstring);
                
            if($this->property['debugmode']=="1")
            {
                echo '<div><b>Ergebniss:</b><br>'.$pv.'</div>';
            }
                
            if($pv!="0")
            {
                $error=$this->property['errortext'];
            }
            if($error!="")
            {
                return array($this->id => $error);
            }
        }
        return false;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox('Title','bezeichnung');
        $html.=parent::getEditorProperty_Textarea("Description",'beschreibung');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("Prove SQL-Statment. Result can only a single value. (Variables: #INDEX1#, #INDEX2#..., #ELEMENT.customerid#)",'sqlprove');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Errormessage, if result from the sql <> 0",'errortext');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,false,false, false);
        return $html;
    }

}

?>