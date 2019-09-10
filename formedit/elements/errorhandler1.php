<?php

class errorhandler1 extends basecontrol
{
    var $name="errorhandler1";

    var $editorname="Errorhandler 1";
    var $editorcategorie="Error";
    var $editorshow=true;
    var $editordescription='Error handler, which checks two fields. These must each have a unique ID. If one of the fields is filled, the other must also be filled.';

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
            $e1has=false;
            $e2has=false;
            $e1id="";
            $e2id="";
            $oelements=$this->interpreterGetElements();
            if($oelements!=null)
            {
                //echo "123";
                foreach($oelements as $oe)
                {
                    if($oe->getCustomerId()==$this->property['idfield1'])
                    {
                        $e1id=$oe->id;
                        //echo "e1".$oe->getInterpreterRequestValue();
                        if($oe->getInterpreterRequestValue()!="")
                        {
                            $e1has=true;
                        }
                    }
                    if($oe->getCustomerId()==$this->property['idfield2'])
                    {
                        $e2id=$oe->id;
                        //echo "e2".$oe->getInterpreterRequestValue();
                        if($oe->getInterpreterRequestValue()!="")
                        {
                            $e2has=true;
                        }
                    }
                }
            }
            
            $emid=$this->id;
            if($e1has==true && $e2has==false)
            {
                //echo "1";
                $error=$this->property['errortext'];
                $emid=$e2id;
            }
            elseif($e1has==false && $e2has==true)
            {
                //echo "2";
                $error=$this->property['errortext'];
                $emid=$e1id;   
            }
            if($error!="")
            {
                //echo "3";
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
        $html.=parent::getEditorProperty_Textbox("ID field 2",'idfield2');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'errortext');
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }

}

?>