<?php

class tabs extends basecontrol
{
    public $name="tabs";

    public $editorname="Tabs";
    public $editorcategorie="Style";
    public $editorshow=true;

    public $isparentcontrol=true;


    public function interpreterInit()
    {
        parent::interpreterInit();
    }

    protected $_aDisplayConditions=null;
    public function getDisplayCondition($bExecute=false)
    {
        if($this->_aDisplayConditions===null)
        {
            $aDisplayConditionsTmp = explode("\n",$this->property['displaycondition']);
            $aDisplayConditions=[];
            for($x=0;$x<count($aDisplayConditionsTmp);$x++)
            {
                if(strpos($aDisplayConditionsTmp[$x],"=")!==false)
                {
                    $aTmp = explode("=",$aDisplayConditionsTmp[$x]);
                    $sName = $aTmp[0];
                    if($sName!="")
                    {
                        unset($aTmp[0]);
                        $sSql = implode("=",$aTmp);
                        $aDisplayConditions[$sName]=$sSql;
                    }
                }
            }

            if($bExecute)
            {
                $oConfig = hsconfig::getInstance();

                $aDisplayConditionsTmp=$aDisplayConditions;
                $aDisplayConditions=[];
                foreach($aDisplayConditionsTmp as $sName=>$sSql)
                {
                    $sSql=$oConfig->parseSQLString($sSql);
                    //echo $sSql;
                    $aDisplayConditions[$sName] = $oConfig->getScalar($sSql);
                }
            }
            else
            {
                //fake all should display in editor
                $aDisplayConditionsTmp=$aDisplayConditions;
                $aDisplayConditions=[];
                foreach($aDisplayConditionsTmp as $sName=>$sSql)
                {
                    $aDisplayConditions[$sName] = "1";
                }
            }
            return $this->_aDisplayConditions = $aDisplayConditions;
        }

        return $this->_aDisplayConditions;
    }
    
    public function getSelectedTabId()
    {
        // stored in cookie when tab is clicked.
        $tabsCookie = json_decode($_COOKIE["fe-tabs"] ?? "{}");
        $selfCookieKey = "$this->containerid-$this->id";
        $id = $tabsCookie->$selfCookieKey ?? 0;

        return $id;
    }
    public function getSelectedTab()
    {
        $id=$this->getSelectedTabId();
        
        $tabs=trim($this->property['tabs']);
        if($tabs!="")
        {
            $aDisplayConditions = $this->getDisplayCondition();

            $tabs=explode("|",$tabs);
            if(count($tabs)<=$id)
                $id=0;
            else
            {
                $sName = $tabs[$id];
                if($aDisplayConditions[$sName]!="")
                {
                    //should make better and search for the next enabled tab
                    $id=0;
                }
            }
        }
        return $this->containerid."_".$this->id."_".$id;
    }
    public function editorBeforeRender()
    {
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        
        $e="";
        $name=trim($this->property['name']);
        $tabs=trim($this->property['tabs']);
        
        if($tabs!="")
        {   
            $tabs=explode("|",$tabs);
            
            $e.= '<script type="text/javascript">    
               
                function tabclicked'.$interpreterid.$this->name.$this->id.'(id, tabid)
                {
                    $("#" + id).parent().children().removeClass("selected");
                    $("#" + id).addClass("selected");
                    ';
    
                    for($xx=0;$xx<count($tabs);$xx++)
                    {
                        $tmpid2   = $this->containerid."_".$this->id."_".$xx;
                        $e.='
                        $("div[data-hasparentcontrol='.$tmpid2.']").css("display","none");
                        ';
                    }
                  
                    $e.='
                    $("div[data-hasparentcontrol=" + id + "]").css("display","block"); 
                    
                    var params={"containerid": "'.$this->containerid.'", "classname": "'.$this->name.'", "id": "'.$this->id.'", "tabid": tabid, "session_name":"'.session_name().'" };
    
                    $.ajax({
                        type: "POST",
                        cache: false,
                        url: "editor_settab.php",
                        data: params,
                        dataType: "html",
                    });
                }
            </script>';
            /* tabclicked'.$interpreterid.$this->name.$this->id.'("'.$this->getSelectedTab().'","'.$this->getSelectedTabId().'"); */
        }
        return $e;
    } 
    public function getEditorRender($text="")
    {
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();

        $csswidth = "width:".$this->width."px;";

        /*resize:both; overflow:scroll; overflow-y: hidden; overflow-x: hidden;*/
        $e = '<div class="element" id="'.$this->id.'" style="left:'.$this->left.'px; top:'.$this->top.'px; '.$csswidth.' height:'.$this->height.'px; z-index:1;  ">
        <input type="hidden" name="classname" value="'.get_class($this).'">
        <input type="hidden" name="containerid" value="'.$this->containerid.'">';
        
        //$e.='$_SESSION['.$this->containerid.']['.$this->name.']['.$this->id.']<br>';
        //$e.=$this->getSelectedTab().'<br>';
        
        $name=trim($this->property['name']);
        $tabs=trim($this->property['tabs']);
        if($tabs!="")
        {
            $tabs=explode("|",$tabs);
            for($x=0;$x<count($tabs);$x++)
            {

                $aDisplayConditions = $this->getDisplayCondition();

                $tmpid   = $this->containerid."_".$this->id."_".$x;
                $e.= '<button 
                class="tabbutton '.($this->getSelectedTab()==$tmpid?'selected':'').'"
                type="button" 
                id="'.$tmpid.'"
                onclick="tabclicked'.$interpreterid.$this->name.$this->id.'(this.id, '.$x.'); "
                style="width:calc('.(100/count($tabs)).'% - 10px); "
                >';
                $e.=$tabs[$x].($name!=""?" - ":"").$name;
                if(isset($aDisplayConditions[$tabs[$x]])) $e.=" *";
                $e.='</button>';
            }
        }
        else
        {
          $e.='&nbsp;'.($text!=""?$text." (":"").$this->editorcategorie.' - '.$this->editorname.($text!=""?")":"");
        }
        $e.= '</div>';
        return $e;
    }

    public function interpreterBeforeRender()
    {
        $conf = getHsConfig();
        $interpreterId = $conf->getInterpreterId();

        $e = "";
        $tabs = trim($this->property['tabs']);

        if ($tabs) {
            $js = <<<js
function loadVisibleIframes() {
    $("iframe").each(function () {
        self = $(this);
        if (self.is(":visible") && !self.attr("src")) { // load iframes that have not been loaded
            self.attr("src", self.data("src"));
        }
    });
}
$(loadVisibleIframes);

function feTabClicked(containerId, tabsId, id, tabid)
{
    var self = $("#" + id);
    self.parent().children().removeClass("selected").removeClass("ui-state-hover");
    self.addClass("selected").addClass("ui-state-hover");
    
    $("[data-hasparentcontrol^=" + id.substring(0, id.lastIndexOf("_")) + "]").css("display","none");
    $("[data-hasparentcontrol=" + id + "]").css("display","block"); 
    
    // set cookie with requested tab.
    var tabs = JSON.parse(Cookies.get("fe-tabs") || "{}");
    tabs[containerId + "-" + tabsId] = tabid;
    Cookies.set("fe-tabs", tabs, { expires: 15 });
    
    loadVisibleIframes();
}
js;
            $e = <<<html
<script>
$js
</script>
html;
        }

        return $e;
    }

    public function getInterpreterRender()
    {
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();

        $csswidth = "width:".$this->width."px;";
        if($this->property['fixwidth']=="0")
        {
            $csswidth = "width:calc(100% - ".($this->left * 2)."px);";
        }

        $e = '<div data-customerid="'.$this->getCustomerId().'" 
            class="elementtab '.$this->property['classname'].'" 
            id="'.$this->id.'" 
            style="text-align:right; '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; '.$csswidth.' height:'.$this->height.'px; line-height:'.$this->height.'px; ">';
        
        $tabs=$this->property['tabs'];
        $tabs=explode("|",$tabs);
        if(count($tabs)>0)
        {
            $aDisplayConditions=$this->getDisplayCondition(true);

            for($x=0;$x<count($tabs);$x++)
            {
                $width = 100 / count($tabs);
                $tmpid   = $this->containerid."_".$this->id."_".$x;
                $otherClass = $this->getSelectedTab() == $tmpid ? 'selected ui-state-hover' : '';
                $e.= "<button class='tabbutton $otherClass' 
                    type=button 
                    id=$tmpid 
                    style='width:calc($width% - 10px)'
                    ";
                if($aDisplayConditions[$tabs[$x]]!="")
                {
                    $e.=" disabled='disabled' ";
                    $e.=" title='".$aDisplayConditions[$tabs[$x]]."' ";
                }
                if($this->getCustomerId()!="")
                    $e.=" data-customerid='".$this->getCustomerId()."_".$x."' ";
                $e.= " onclick=feTabClicked('$this->containerid','$this->id',this.id,$x)>
                        {$tabs[$x]}</button>";
            }
        }
        
        $e.= '</div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        
        $html.=parent::getEditorProperty_Textbox("Name",'name');
        $html.=parent::getEditorProperty_Textarea("Tabs (seperate with |. e.g. tab1|tab2|tab3)",'tabs');
        $html.=parent::getEditorProperty_Textarea("Should the tab disable? One condition per line. Schema: TABNAME=SQL which return a string which use as tooltip, if nothing return (empty string) the button stay enabled",'displaycondition');

        $html.=parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');

        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        return $html;
    }
}
