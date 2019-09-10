<?php

class property_change_php extends basecontrol
{
    var $name="property_change_php";

    var $editorname="Property change PHP";
    var $editorcategorie="Control";
    var $editorshow=true;
    var $editordescription='Change any property before loading an element in the interpreter. The value must be returned by a php script. If nothing is returned, the property is not set. It remains the standard.';

    var $_propertyold=null;
    var $_propertynew=null;
    var $_propertycode=null;

    public function getInterpreterRender()
    {
        $e="";

        if($this->property['debugmode']=="1")
        {
            $e = '<div class="'.$this->property['classname'].'" id="'.$this->id.'" style="overflow:scroll; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:12px; background-color:green; color:white; '.$this->property['style'].' ">
                '.$this->property['bezeichnung'].'<br>
                PHP: <pre>'.print_r($this->_propertycode,true).'</pre>
                OLD: <pre>'.print_r($this->_propertyold,true).'</pre>
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
            /**
             * @var basecontrol $oe
             */
            foreach($oelements as $oe)
            {
                if($oe->getCustomerId()==$this->property['elementid'])
                {
                    $this->_propertyold[$this->property['elementid']]=$oe->property;

                    if(trim($this->property['phpcode'])!="")
                    {
                        $hsconfig=getHsConfig();
                        $phpcode = $this->property['phpcode'];
                        $phpcode=$hsconfig->parseSQLString($phpcode);

                        $this->_propertycode[$this->property['elementid']]=$phpcode;

                        $value="";
                        try
                        {
                            $value = eval($phpcode);
                        }
                        catch (Exception $e)
                        {
                            echo '<div>';
                            echo "ERROR in php code (".$this->name."): ".$e->getMessage()."<br>";
                            echo "SOURCE: ".$phpcode."<br>";
                            echo '</div>';
                        }
                        
                        if($value!=="")
                        {
                            $oe->property[$this->property['elementproperty']]=$value;
                        }
                        $this->_propertynew[$this->property['elementid']]=$oe->property;
                    }
                    break;
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
        $html.=parent::getEditorProperty_Textbox("Customer ID from the element that should change:",'elementid');
        $html.=parent::getEditorProperty_Textbox("Property which should change:",'elementproperty');
        $html.=parent::getEditorProperty_TextareaPHP("Phpcode that execute by command eval. When the code 'return' a value,
        this will be use as new value for the property.
        You can include files. Use always 'require_once'.
        Standard variables like #INDEX1#, #INDEX2#... will first replace in the code
        by the real values before executing.",'phpcode');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }

}

?>