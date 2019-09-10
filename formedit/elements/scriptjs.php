<?php

class scriptjs extends basecontrol
{
    var $name="scriptjs";

    var $editorname="ScriptJS";
    var $editorcategorie="Script Elements";
    var $editorshow=true;
    var $editordescription='Javascript function that gets execute every second. If it returns "true" a php script gets called and the output display in the element. All data from the form is send to the php script. If a element has a customerid, it used additional as POST parameter.';


    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        
        $e = '<div data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().'text-align:left; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.($this->property['fixwidth'] == "0" ? "calc(100% - " . ($this->left * 2) . "px)" : "{$this->width}px").'; height:'.$this->height.'px; line-height:20px; '.$this->property['style'].' ">
            <div id="ajaxcontent'.$this->id.'">'.$this->property['bezeichnung'].'</div>
        </div>
        <script type="text/javascript">
            var tt_'.$interpreterid.$this->name.$this->id.';
            function tickertimer_'.$interpreterid.$this->name.$this->id.'()
            {
                tt_'.$interpreterid.$this->name.$this->id.' = setTimeout(function() {
                    tickerprove_'.$interpreterid.$this->name.$this->id.'();       
                }, 1000);
            }
            function tickerprove_'.$interpreterid.$this->name.$this->id.'()
            {
                if(ticker_'.$interpreterid.$this->name.$this->id.'()==true)
                {
                    callback_'.$interpreterid.$this->name.$this->id.'();
                }
                else
                {
                    tickertimer_'.$interpreterid.$this->name.$this->id.'();
                } 
            }
            function ticker_'.$interpreterid.$this->name.$this->id.'()
            {
                '.$this->property['javascript'].'
            }
            function callback_'.$interpreterid.$this->name.$this->id.'()
            {
                var param="project='.$hsconfig->getProjectName().'&elementclass='.$interpreterid.$this->name.'";
                param+="&elementid='.$this->id.'";
                param+="&elementfunction=getInterpreterRenderAjax";
                param+="&project=" + $("#fe-project").val();
                param+="&";
                param+=$("#formular").serialize();
                
                $("#formular select[data-customerid]").each(function( index ) {
                    if($(this).attr("data-customerid")!="")
                    {
                        var name=$(this).attr("data-customerid");
                        var value= $(this).val();
                        param+="&"+name+"="+value;
                    }
                });
                
                $( "#formular input[data-customerid]" ).each(function( index ) {

                    if($(this).attr("data-customerid")!="")
                    {
                        var name=$(this).attr("data-customerid");
                        var value="";
                        
                        if($(this).attr("type")=="checkbox")
                        {
                            value=0;
                            if( $(this).attr("checked"))
                            {
                                value=1;
                            }
                        }
                        else
                        {
                            value = $(this).val();
                        }
                        param+="&"+name+"="+value;
                    }
                });
                ';
                if($this->property['greyout']=="1")
                    $e.= '$("#ajaxcontent'.$this->id.'").css("opacity","0.5"); ';
                $e.='
                $.ajax({
                    type: "POST",
                    url: "'.$hsconfig->getBaseUrl().'/interpreter_ajax.php",
                    data: param,
                    
                    success: function(data)
                    {
                        $("#ajaxcontent'.$this->id.'").html(data);
                    },
                    complete: function() {
                        ';
                        if($this->property['greyout']=="1")
                            $e.= '$("#ajaxcontent'.$this->id.'").css("opacity","1.0"); ';
                        $e.='
                        tickertimer_'.$interpreterid.$this->name.$this->id.'();
                    }
                });
            }
            tickerprove_'.$interpreterid.$this->name.$this->id.'();
            
        </script>
        ';
        return $e;
    }
    public function getInterpreterRenderAjax()
    {
        $hsconfig = getHsConfig();

        $debugmode = $this->property['debugmode'];

        if ($debugmode == "1") {
            $ticker = $_SESSION['ticker'];
            if (is_numeric($ticker) == false)
                $ticker = 0;
            $ticker++;

            $_SESSION['ticker'] = $ticker;
            echo "Ticker: " . $ticker . "<br>";
        }

        $file = $this->property['phpscript'];

        $path = "";
        if ($projectBaseDir = $hsconfig->getProjectBaseDir()) {
            $path = "$projectBaseDir/scriptphp2/$file";
            if (!file_exists($path)) {
                $path = "";
            }
        }
        if ($path == "") {
            $path = $hsconfig->getBaseDir() . "/scriptphp2/" . $file;
        }

        ob_start();
        include_once($path);
        $return = ob_get_contents();
        ob_end_clean();

        echo $return;
        die("");
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Textarea("Javascript function (only the inner part): function ticker() { .... }","javascript");
        /*$html.=parent::getEditorProperty_Textarea("PHP function (only the inner part) function ajax_callback() { .... }","phpscript");*/
        /*$html.=parent::getEditorProperty_SelectboxScriptphp2("PHP Script that gets execute (Folder 'scriptphp2')",'phpscript');*/
        $html.=parent::getEditorProperty_Textbox("PHP Script that gets execute (Folder 'scriptphp2')",'phpscript');
        $html.=parent::getEditorProperty_Checkbox("Grey out on ajaxcall",'greyout','1');
        $html.=parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html .= parent::getEditorProperty_Line();
        //$html.=parent::getEditorProperty_Textbox("CSS-style",'style');
        //$html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html .= parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}

?>
