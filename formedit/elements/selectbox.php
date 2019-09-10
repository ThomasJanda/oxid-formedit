<?php

class selectbox extends basecontrol
{
    var $name = "selectbox";

    var $editorname = "Selectbox";
    var $editorcategorie = "Database Items";
    var $editorshow = true;
    var $editordescription = 'Select box, which the user can select different preset texts';


    public function getInterpreterRender()
    {
        $value = parent::getInterpreterRequestValue();

        $e = "";
        if ($this->property['readonly'] == "1")
        {
            $e .= '<input data-customerid="' . $this->getCustomerId() . '" type="hidden" name="' . $this->id . '" value="' . $value . '">';
        }
        $e .= '<div data-customeridbox="' . $this->getCustomerId() . '" data-hasparentcontrol="' . $this->getParentControl() . '" class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->getParentControlCss() . '' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <select 
                ';
        if ($this->property['readonly'] != "1")
        {
            $e .= 'data-customerid="' . $this->getCustomerId() . '" ';
        }
        if ($value) {
            $e .= 'data-original="' . $value . '" ';
        }
        $e .= '
                id="' . $this->id . 'selectbox"
                name="' . $this->id . '" 
                style="vertical-align:middle; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; border:1px solid #dddddd; ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'border-color:red; ' : '') . ' "
                tabindex="' . $this->property['taborder'] . '"
                ' . ($this->property['readonly'] == "1" ? 'readonly="readonly" disabled="disabled"' : "") . '
            >
            ';

        $values = $this->property['standardvalues'];
        $values = explode("|", $values);
        foreach ($values as $v)
        {
            $tmp = explode(":", $v);
            $e .= "<option value='" . $tmp[0] . "' " . ($tmp[0] == $value ? 'selected' : '') . ">" . $tmp[1] . "</option>";
        }

        $e .= '</select></div>';


        $setreadonly = trim($this->property['setreadonly']);
        if ($setreadonly != "")
        {
            $hsconfig = getHsConfig();
            $interpreterid = $hsconfig->getInterpreterId();
            $setreadonly = explode("\n", str_replace("\r", "", $setreadonly));
            $all = array();
            $readonly = array();
            foreach($setreadonly as $line)
            {
                $tmp = explode("|", $line);
                $v = $tmp[0];
                $cids = explode(":", $tmp[1]);

                $readonly[$v] = $cids;
                $all = array_merge($all, $cids);
            }
            $all = array_unique($all);


            $e .= '<script type="text/javascript">
            function setReadOnly' . $interpreterid . $this->name . $this->id . '()
            {
                var v = $("#' . $this->id . 'selectbox").val();
                ';
                foreach ($all as $customeridbox)
                {
                    //enable
                    $e .= '
                    if($( "#formular div[data-customeridbox='.$customeridbox.']" ).length)
                        $( "#formular div[data-customeridbox=' . $customeridbox . '] .enableelement_' . $interpreterid . $this->name . $this->id . '" ).css("display","none");
                    if($( "#formular button[data-customerid='.$customeridbox.']" ).length)
                        $( "#formular button[data-customerid=' . $customeridbox . ']" ).removeAttr("disabled");
                     ';
                }
                foreach($readonly as $v=>$cids)
                {
                    $e.='if(v=="'.$v.'") {';
                        foreach($cids as $customeridbox)
                        {
                           $e.='
                           if($( "#formular div[data-customeridbox='.$customeridbox.']" ).length)
                              $( "#formular div[data-customeridbox='.$customeridbox.'] .enableelement_'.$interpreterid.$this->name.$this->id.'" ).css("display","block");
                           if($( "#formular div[data-customeridbox='.$customeridbox.'] input[type=checkbox]" ).length) 
                              $( "#formular div[data-customeridbox='.$customeridbox.'] input[type=checkbox]" ).prop("checked", false); 
                           if($( "#formular button[data-customerid='.$customeridbox.']" ).length) 
                           {
                                var reselect = false;
                              if($( "#formular button[data-customerid='.$customeridbox.']" ).parent().hasClass("elementtab"))
                              {
                                  /* is tab element */
                                  if($( "#formular button[data-customerid='.$customeridbox.']" ).hasClass("selected"))
                                  {
                                      /* tab which should disabled is selected */
                                      reselect = true;
                                  }
                              }
                              $( "#formular button[data-customerid='.$customeridbox.']" ).prop("disabled", true);
                              if(reselect)
                              {
                                /* click on the first not disabled tab */
                                $( "#formular button[data-customerid='.$customeridbox.']" ).parent().find("button:enabled:first").click();
                              }
                           }
                           ';
                        }
                    $e.='}';
                }
            $e.='}
            
            function init_elements_'.$interpreterid.$this->name.$this->id.'()
            {
                ';
                foreach($all as $customeridbox)
                {
                    $e.='if($( "#formular div[data-customeridbox='.$customeridbox.']" ).length)
                        $( \'<div class="enableelement_clipboard enableelement_'.$interpreterid.$this->name.$this->id.'" style="position:absolute; left:0px; right:0px; top:0px; bottom:-5px; background-color:white; opacity:0.5; z-index:10000; display:none; "></div>\' ).appendTo( $( "#formular div[data-customeridbox='.$customeridbox.']" ) ); 
                        ';
                }
                $e.='
            }
            
            $(function() {
                init_elements_'.$interpreterid.$this->name.$this->id.'();
                $("#'.$this->id.'selectbox").change(function() {
                    setReadOnly'.$interpreterid.$this->name.$this->id.'();
                });
                setReadOnly'.$interpreterid.$this->name.$this->id.'();
            });
            
            </script>';
        }

        return $e;
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Values. Schema: value:text|value:text... ",'standardvalues');
        $html.=parent::getEditorProperty_Textbox("Value that gets selected at the beginning",'standardvalue');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("Set readonly, if a value is selected. Schema: value|customerid:customerid... One Value with the Customer Ids per line.",'setreadonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
}

?>
