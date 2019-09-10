<?php
include_once('checkbox.php');

class checkbox_required extends checkbox
{
    var $name="checkbox_required";

    var $editorname="Checkbox required";
    var $editorcategorie="Error";
    var $editorshow=true;
    var $editordescription='If checkbox checked, some fields get enable';

    public function getInterpreterRender()
    {
        $checked=($this->property['checked']=="1"?true:false);

        $e = '<div 
            data-customeridbox="'.$this->getCustomerId().'" 
            data-hasparentcontrol="'.$this->getParentControl().'" 
            class="'.$this->property['classname'].'" 
            id="'.$this->id.'" 
            style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
        <input 
            data-customerid="'.$this->getCustomerId().'" 
            type="checkbox" 
            name="'.$this->id.'" 
            value="1" 
            style="vertical-align:middle; "
            tabindex="'.$this->property['taborder'].'"
            >
            '.$this->property['title'];

        $setreadonly=trim($this->property['setreadonly']);
        if($setreadonly!="")
        {
            $hsconfig = getHsConfig();
            $interpreterid = $hsconfig->getInterpreterId();
            $setreadonly=explode("\n",str_replace("\r","",$setreadonly));

            $checked_condition = $this->property['checked_condition'];
            $sJavaScriptId = $this->id;
            $sJavaScriptUnique = $interpreterid.$this->name.$this->id;

            $sJavaScriptEnable="";
            foreach($setreadonly as $customeridbox) {
                $sJavaScriptEnable .= <<<JS
                $( "#formular div[data-customeridbox={$customeridbox}] .enableelement_{$sJavaScriptUnique}" ).css("display","none");
JS;
            }

            $sJavaScriptDisable="";
            foreach($setreadonly as $customeridbox)
            {
                $sJavaScriptDisable.= <<<JS
                $( "#formular div[data-customeridbox={$customeridbox}] .enableelement_{$sJavaScriptUnique}" ).css("display","block");
                try {
                    $( "#formular div[data-customeridbox={$customeridbox}] input:not([type=checkbox])").val("");
                    $( "#formular div[data-customeridbox={$customeridbox}] input[type=checkbox]").attr("checked", false);
                    $( "#formular div[data-customeridbox={$customeridbox}] textarea").val("");
                    $( "#formular div[data-customeridbox={$customeridbox}] select").find("option:eq(0)").attr("selected","selected");
                } catch(e) {}
JS;
            }

            $sJavaScriptInit="";
            foreach($setreadonly as $customeridbox)
            {
                $sJavaScriptInit.= <<<JS
                $('<div class="enableelement_clipboard enableelement_{$sJavaScriptUnique}" style="position:absolute; left:0px; right:0px; top:0px; bottom:-5px; background-color:white; opacity:0.5; z-index:10000; display:none; "></div>' ).appendTo( $( "#formular div[data-customeridbox={$customeridbox}]" ) ); 
JS;
            }

            $sJavaScript = <<<JS
            function enable_elements_{$sJavaScriptUnique}()
            {
                {$sJavaScriptEnable}
            }
            function disable_elements_{$sJavaScriptUnique}()
            {
                {$sJavaScriptDisable}
            }
            function init_elements_{$sJavaScriptUnique}()
            {
                {$sJavaScriptInit}
            }
            $(function() {
                init_elements_{$sJavaScriptUnique}();
            
                if('{$checked_condition}'!="")
                {
                    let checked = false;
                    
                    /*test if the condition field contain a value*/
                    if($("#formular div[data-customeridbox={$checked_condition}] input:not([type=checkbox])").length)
                    {
                        if($("#formular div[data-customeridbox={$checked_condition}] input:not([type=checkbox])").val()!="")
                            checked = true;
                    }
                    if($("#formular div[data-customeridbox={$checked_condition}] input[type=checkbox]").length)
                    {
                        if($("#formular div[data-customeridbox={$checked_condition}] input[type=checkbox]").attr('checked')=="checked")
                            checked=true;
                    }
                    if($("#formular div[data-customeridbox={$checked_condition}] textarea").length)
                    {
                        if($("#formular div[data-customeridbox={$checked_condition}] textarea").val()!="")
                            checked=true;
                    }
                    if($("#formular div[data-customeridbox={$checked_condition}] select").length)
                    {
                        if($("#formular div[data-customeridbox={$checked_condition}] select").val()!="")
                            checked=true;
                    }
                    
                    if(checked)
                    {
                        $("#{$sJavaScriptId} input[type=checkbox]").attr('checked','checked');
                        enable_elements_{$sJavaScriptUnique}();
                    }                     
                    else
                        disable_elements_{$sJavaScriptUnique}(); 
                        
                    $("#{$sJavaScriptId} input[type=checkbox]:checkbox").change(function () {
                        var check = $(this).attr("checked");
                        if(check=="checked")
                        {
                            enable_elements_{$sJavaScriptUnique}();
                        }
                        else
                        {
                            disable_elements_{$sJavaScriptUnique}();
                        }
                    });
                }
                else 
                    disable_elements_{$sJavaScriptUnique}(); 
            });
JS;
            $e.='<script type="text/javascript">'.$sJavaScript.'</script>';
        }

        $e.='</div>';
        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['title']);
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Text",'title');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Set checked if field has value at startup (Customer id)",'checked_condition');
        $html.=parent::getEditorProperty_Textarea("Set readonly, if unchecked. List with 'Customer IDs' from elements which should enable/disable. (One id per line)",'setreadonly');
        $html.=parent::getEditorPropertyFooter(true,false);
        return $html;
    }

}

?>
