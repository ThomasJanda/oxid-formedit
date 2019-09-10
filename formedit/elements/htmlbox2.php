<?php

class htmlbox2 extends basecontrol
{
    var $name="htmlbox2";

    var $editorname="Htmlbox2";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Html editor';

    protected $interpreterAfterSave_table="";
    protected $interpreterAfterSave_col="";
    protected $interpreterAfterSave_index="";
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $this->_interpreterSave($table,$colindex,$indexvalue);
        return parent::interpreterSaveNew($table, $colindex, $indexvalue);
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $this->_interpreterSave($table,$colindex,$indexvalue);
        return parent::interpreterSaveEdit($table, $colindex, $indexvalue);
    }
    protected function _interpreterSave($table, $colindex, $indexvalue)
    {
        $this->interpreterAfterSave_table=$table;
        $this->interpreterAfterSave_col=$colindex;
        $this->interpreterAfterSave_index=$indexvalue;
    }

    public function interpreterAfterSaveNew()
    {
        $this->_interpreterAfterSave();
    }
    public function interpreterAfterSaveEdit()
    {
        $this->_interpreterAfterSave();
    }
    protected function _interpreterAfterSave()
    {
        $table = $this->interpreterAfterSave_table;
        $colindex = $this->interpreterAfterSave_col;
        $indexvalue = $this->interpreterAfterSave_index;

        $dbfield=$this->property['datenbankspalte'];
        if(trim($dbfield)!="" && $table!="")
        {
            $hsconfig=getHsConfig();

            $onlysource=false;
            if(isset($_REQUEST[$this->id.'_onlysource']) && $_REQUEST[$this->id.'_onlysource']=="1")
                $onlysource=true;

            $sqlstring = "SELECT count(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '".$hsconfig->dbName."'
            AND TABLE_NAME = '".$table."'
            AND COLUMN_NAME = '".$dbfield."_onlysource'";
            if($hsconfig->getScalar($sqlstring)!="0") {
                $sql="update `".$table."` set `".$dbfield."_onlysource`=".($onlysource?1:0)." where `".$colindex."`= '".$hsconfig->escapeString($indexvalue)."'";
                $hsconfig->executeNoReturn($sql);
            }
        }
    }
    

    public function interpreterBeforeRender()
    {
        $hsconfig=getHsConfig();
        $path=$hsconfig->getBaseUrl();
        $html='<script type="text/javascript" src="'.$path.'/js/ckeditor/ckeditor.js"></script>';
        //$html='<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.2/ckeditor.min.js"></script>';
        return $html;
    }

    public function getInterpreterRender()
    {
        $value=parent::getInterpreterRequestValue();
        $hsconfig=getHsConfig();
        
        $onlysource=false;
        if(isset($_REQUEST[$this->id.'_onlysource']))
        {
             if($_REQUEST[$this->id.'_onlysource']=="1")
             {
                 $onlysource=true;
             }
        }
        else
        {
            $dbfield=$this->property['datenbankspalte'];
            
            $otab=$this->getTab();
            $table = $otab->property['table'];
            $colindex=$otab->property['colindex'];
            $indexvalue=$hsconfig->getIndex1Value();

            $sqlstring = "SELECT count(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '".$hsconfig->dbName."'
            AND TABLE_NAME = '".$table."'
            AND COLUMN_NAME = '".$dbfield."_onlysource'";
            if($hsconfig->getScalar($sqlstring)!="0") {
                $sqlstring = "select `" . $dbfield . "_onlysource` from `" . $table . "` where `" . $colindex . "`='" . $hsconfig->escapeString($indexvalue) . "'";
                if ($hsconfig->getScalar($sqlstring) == "1") {
                    $onlysource = true;
                }
            }
        }
    
        
        $path=$hsconfig->getBaseUrl();

        $picurl=$this->property['url'];
        $picuri=$this->property['uri'];
        
        $e = '<div data-customeridbox="'.$this->id.'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' border:0px solid black; overflow:hidden; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px;  '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <textarea 
                id="editor_'.$this->id.'"
                data-customerid="'.$this->id.'" 
                name="'.$this->id.'" 
                style="width:'.$this->width.'px; height:'.($this->height-30).'px;  "
                tabindex="'.$this->property['taborder'].'"
            >'.$value.'</textarea>
            <div style="height:20px; ">
                <input type="hidden" name="'.$this->id.'_onlysource" value="0">
                <input type="checkbox" name="'.$this->id.'_onlysource" id="editor_checkbox_'.$this->id.'" '.($onlysource?'checked':'').' value="1" onchange="emwysiwyg_changeeditor'.$this->id.'();">
                Only source
            </div>
            
            <script type="text/javascript">
                var editor_'.$this->id.' = null;
                var editor_'.$this->id.'_heightchanged = false;
                function emwysiwyg_starteditor'.$this->id.'()
                {
                    editor_'.$this->id.' = CKEDITOR.replace( "editor_'.$this->id.'", 
                    { 
                        height: "'.$this->height.'",
                        filebrowserBrowseUrl : "'.$path.'/js/ckeditor/filebrowser/index.php?picurl='.base64_encode($picurl).'&picuri='.base64_encode($picuri).'&uid='.uniqid("").'",
                        filebrowserWindowWidth : "900",
                        filebrowserWindowHeight : "500",
                        
                        on: 
                        {
                            instanceReady: function( evt ) {
                                emwysiwyg_changeheight'.$this->id.'();
                            }
                        }
                    });
                }
                function emwysiwyg_changeeditor'.$this->id.'()
                {
                    if(document.getElementById("editor_checkbox_'.$this->id.'").checked)
                    {
                        if(editor_'.$this->id.')
                        {
                            editor_'.$this->id.'.destroy();
                            editor_'.$this->id.'=null;
                            
                            var tmp = document.getElementById("editor_'.$this->id.'").value;
                            replace( /&gt;/g   ,\'>\');
                            replace( /&quot;/g ,\'"\');
                            replace( /&lt;/g   ,\'<\');
                            document.getElementById("editor_'.$this->id.'").value = tmp;
                        } 
                    }
                    else
                    {
                        if(!editor_'.$this->id.')
                        {
                            emwysiwyg_starteditor'.$this->id.'();
                        }
                    }
                }
                function emwysiwyg_changeheight'.$this->id.'()
                {
                    editor_'.$this->id.'_heightchanged=true;
                    var height1 = $("#'.$this->id.' .cke_top").outerHeight();
                    var height2 = $("#'.$this->id.' .cke_bottom").outerHeight();
                    var height3 = '.($this->height-20).' - height1 - height2;
                    $("#'.$this->id.' .cke_contents").css("height",height3 + "px");
                }
           
                /* if($("#'.$this->id.'").css("display")=="none") */
                {
                    $("#'.$this->id.'").bind("DOMAttrModified propertychange", function(e) {
                        /* if(editor_'.$this->id.'_heightchanged==false) */
                        {
                            if(e.attrName=="style")
                            {
                                if($("#'.$this->id.'").css("display")=="block")
                                {
                                    if(editor_'.$this->id.')
                                    {
                                        emwysiwyg_changeheight'.$this->id.'();
                                    }
                                }           
                            }                            
                        }
                    });    
                }    

                
                ';
                if(!$onlysource)
                    $e.='emwysiwyg_starteditor'.$this->id.'(); ';
            $e.='</script>
        </div>';
        
        
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Absolute path on the server to the savepath for pictures without '/' at the end",'uri');
        $html.=parent::getEditorProperty_Textbox("URL to the  savepath for pictures (http://) without '/' at the end",'url');
        $html.=parent::getEditorPropertyFooter(true,true,true,true);
        return $html;
    }

	function getSQL($table)
	{
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` TEXT; 
alter table `".$table."` add column `".$dbfield."_onlysource` TINYINT(1) DEFAULT 0; ";
	}
}

?>
