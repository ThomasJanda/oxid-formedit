<?php

class help extends basecontrol
{
    var $name="help";

    var $editorname="Help";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Shows a "?" with a help popup';

    public static function interpreterFinishJavascript_static() {
        //load libary
        $sRet = <<<js
        $.getScript("https://unpkg.com/tippy.js@3/dist/tippy.all.min.js", function() {
            $(".tooltipicon").each(function( index ) {
                let idicon = $(this).attr('id');
                let idtext = $(this).next().attr('id');
                /*console.log(idicon + " " + idtext);*/
                tippy("#" + idicon, {
                  content: document.querySelector("#" + idtext).innerHTML,
                  arrow: true,
                  animation: "fade",
                  maxWidth:  $(this).attr('data-maxwidth')
                });
            });
        });
js;
        return $sRet;
    }

    public function getInterpreterRender()
    {
        $minwidth=0;
        $maxwidth=$this->property['maxwidth'];
        if($maxwidth==="" || is_numeric($maxwidth)===false || $maxwidth===0)
            $maxwidth=300;

        /*
        $e = '<div data-fixopen="0" data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:20px; height:20px; line-height:20px; cursor:help; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <span id="' . $this->id . 'icon" class="ui-icon ui-icon-help" title=""></span>
            <div id="'.$this->id.'text" style="display:none; background-color:white; position:absolute; top:20px; left:0px; z-index:100; padding:5px; border:1px solid #d3d3d3; border-radius:5px; min-width:'.$minwidth.'px; ">'.nl2br($this->property['helptext']).'</div>
        </div>
        <script type="text/javascript">
            
            $(\'#'.$this->id.'icon\').mouseenter(function() {
                $(\'#'.$this->id.'text\').css(\'display\',\'block\');
            }).mouseleave(function() {
                var fixopen=$(\'#'.$this->id.'text\').attr(\'data-fixopen\');
                if(fixopen!="1")
                {
                    $(\'#'.$this->id.'text\').css(\'display\',\'none\');
                }
            }).click(function() {
                var fixopen=$(\'#'.$this->id.'text\').attr(\'data-fixopen\');
                if(fixopen!="1")
                {
                    $(\'#'.$this->id.'text\').attr(\'data-fixopen\',\'1\');                  
                }
                else
                {
                    $(\'#'.$this->id.'text\').attr(\'data-fixopen\',\'0\');  
                }
            });
        </script>
        ';
        */

        $sText = nl2br($this->property['helptext']);
        if(isset($this->property['picture_SOURCE']) && $this->property['picture_SOURCE']!="")
        {
            $sText='<img src="'.$this->property['picture_SOURCE'].'" style="max-width:'.$maxwidth.'px; " border="1"><br>'.$sText;
        }

        $e="";
        if(trim($sText)!="")
        {
                    $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="tooltipbox '.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:20px; height:20px; line-height:20px; cursor:help; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <span data-maxwidth="'.$maxwidth.'" id="' . $this->id . 'icon" class="tooltipicon ui-icon ui-icon-help" title=""></span>
            <div id="'.$this->id.'text" class="tooltiptext" style="display:none; ">'.$sText.'</div>
        </div>
        ';
        }


        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


    protected function _setEditorProperty_processValue($key, $value)
    {
        $aRet = [$key => $value];
        if($key=="picture")
        {
            $name=$this->name."_".$key."_FILE";
            if(isset($_FILES[$name]))
            {
                $ext_type = array('gif','jpg','jpe','jpeg','png');
                $file_info = pathinfo($_FILES[$name]['name']);
                if(in_array(strtolower($file_info['extension']),$ext_type))
                {
                    $sMime = mime_content_type($_FILES[$name]['tmp_name']);
                    $sContent = base64_encode(file_get_contents($_FILES[$name]['tmp_name']));
                    $sSource = "data:".$sMime.";base64,".$sContent;

                    $aRet[$key]=$_FILES[$name]['name'];
                    $aRet[$key."_SOURCE"] = $sSource;
                }
            }
        }
        elseif($key=="picture_REMOVE")
        {
            $sKey="picture";

            $aRet[$sKey]="";
            $aRet[$sKey."_SOURCE"] = "";
            $aRet[$key]="0";
        }
        return $aRet;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textarea("Helptext",'helptext');
        $html.=parent::getEditorProperty_Textbox("Max-width",'maxwidth', 300);
        $html.=parent::getEditorProperty_FileUpload("Picture", 'picture');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>