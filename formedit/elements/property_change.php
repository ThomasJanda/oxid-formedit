<?php

class property_change extends basecontrol
{
    var $name="property_change";

    var $editorname="Property change";
    var $editorcategorie="Control";
    var $editorshow=true;
    var $editordescription='Change any property before loading an element in the interpreter. The value must be queried using an SQL query. If null or nothing is returned, the property is not set. It remains the standard.';

    var $_propertyold=null;
    var $_propertynew=null;
    var $_provesql="";
    var $_provevalue="";
    public function getInterpreterRender()
    {
        $e="";
        
        if($this->property['debugmode']=="1")
        {
            $e = '<div class="'.$this->property['classname'].'" id="'.$this->id.'" style="overflow:scroll; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:12px; background-color:green; color:white; '.$this->property['style'].' ">
                '.$this->property['bezeichnung'].'<br>
                OLD: <pre>'.print_r($this->_propertyold,true).'</pre>
                SQL: <pre>'.$this->_provesql.'</pre>
                VALUE: <pre>'.print_r($this->_provevalue,true).'</pre>
                NEW: <pre>'.print_r($this->_propertynew,true).'</pre>
            </div>';            
        }

        return $e;
    }

    public function getEditorRender($text="")
    {
        $e=parent::getEditorRender($text);
        $e = '<div class="element" id="'.$this->id.'" style="background-color:green; color:white; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
            '.$this->property['bezeichnung'].' - 
            <input type="hidden" name="classname" value="'.get_class($this).'">
            <input type="hidden" name="containerid" value="'.$this->containerid.'">
            &nbsp;'.($text!=""?$text." (":"").$this->editorcategorie.' - '.$this->editorname.($text!=""?")":"").'
        </div>';
        return $e;
    }
    
    public function interpreterBeforeRender()
    {
        $oelements=$this->interpreterGetElements();
        if($oelements!=null)
        {
            $elementids = $this->property['elementid'];
            $elementids = explode(";",$elementids);

            $hsconfig=getHsConfig();
            $sqlstring = $this->property['sqlvalue'];
            $sqlstring=$hsconfig->parseSQLString($sqlstring);
            $this->_provesql=$sqlstring;

            foreach($elementids as $elementid)
            {
                $elementid = trim($elementid);
                foreach($oelements as $oe)
                {
                    if($oe->getCustomerId()==$elementid)
                    {
                        $this->_propertyold[$elementid]=$oe->property;

                        if(trim($this->property['sqlvalue'])!="")
                        {
                            $pv=$hsconfig->getScalar($sqlstring);
                            if(!is_array($this->_provevalue))
                                $this->_provevalue=array();
                            $this->_provevalue[$elementid]=$pv;

                            if($pv!="")
                                $oe->property[$this->property['elementproperty']]=$pv;

                            $this->_propertynew[$elementid]=$oe->property;
                        }
                        break;
                    }
                }
            }

        }
    }
    
    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox('Title','bezeichnung');
        $html.=parent::getEditorProperty_Textarea("Description",'beschreibung');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Customer ID from the element that should change (you can seperate more customer ids with ';')",'elementid');
        $html.=parent::getEditorProperty_Textbox("Property which should change:",'elementproperty');
        $html.=parent::getEditorProperty_Textarea("SQL that performs the new value. The result must be a single value (Variables: #INDEX1#, #INDEX2#..., #ELEMENT.customerid#)",'sqlvalue');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }

}

?>