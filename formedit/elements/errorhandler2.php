<?php

class errorhandler2 extends basecontrol
{
    var $name="errorhandler2";

    var $editorname="Errorhandler 2";
    var $editorcategorie="Error";
    var $editorshow=true;
    var $editordescription='Error handler in a field checks a value and then use its value performs a SQL exam in another field.';

    public function getInterpreterRender()
    {
        $e="";
        return $e;
    }

    public function getEditorRender($text="")
    {
        $e=parent::getEditorRender($text);
        $e = '<div class="element" id="'.$this->id.'" style="background-color:red; color:white; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
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
        if(isset($this->property['idfield1']) && isset($this->property['idfield2']) && isset($this->property['errortext']))
        {
            $e1id="";
            $e2id="";
            $e1v="";
            $e2v="";
            $oelements=$this->interpreterGetElements();
            if($oelements!=null)
            {
                //echo "123";
                foreach($oelements as $oe)
                {
                    if($oe->getCustomerId()==$this->property['idfield1'])
                    {
                        $e1id=$oe->id;
                        $e1v=trim($oe->getInterpreterRequestValue());
                    }
                    if($oe->getCustomerId()==$this->property['idfield2'])
                    {
                        $e2id=$oe->id;
                        $e2v=trim($oe->getInterpreterRequestValue());
                    }
                }
            }
            
            if($this->property['debugmode']=="1")
            {
                echo '<div><b>Wert1:</b><br>'.$e1v.'</div>';
                echo '<div><b>Wert2:</b><br>'.$e2v.'</div>';
            }
            
            $emid="";
            if($e1v==trim($this->property['valuefield1']))
            {
                $hsconfig=getHsConfig();
                $sqlstring = $this->property['sqlprove'];
                $sqlstring=str_replace('#WERTFELD1#',$e1v,$sqlstring);
                $sqlstring=str_replace('#WERTFELD2#',$e2v,$sqlstring);
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
                    echo '<div><b>Ergebniss:</b><br>'.$pv.'=='.trim($this->property['valuefield2']).'</div>';
                }
                    
                if($pv==trim($this->property['valuefield2']))
                {
                    $error=$this->property['errortext'];
                    $emid=$e2id;
                }
            }

            if($error!="")
            {
                return array($emid => $error);
            }
        }
        return false;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("ID field 1",'idfield1');
        $html.=parent::getEditorProperty_Textbox("Value field 1 equal",'valuefield1');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("ID field 2",'idfield2');
        $html.=parent::getEditorProperty_Textbox("Prove values by sql equal",'valuefield2');
        $html.=parent::getEditorProperty_Textbox("Prove SQL-statment (Variables: #INDEX1#, #INDEX2#..., #WERTFELD1#, #WERTFELD2#)",'sqlprove');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Errormessage, if value2 equal SQL-Statment result",'errortext');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }

}

?>