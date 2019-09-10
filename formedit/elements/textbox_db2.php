<?php

class textbox_db2 extends basecontrol
{
    var $name="textbox_db2";

    var $editorname="Textbox DB2";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Simple textbox where the user can select a value from the database';
    
    
    var $interpreter_page=0;
    var $interpreter_orderby="";
    var $interpreter_orderbyDirection="";
    var $interpreter_kennzeichen1="";
    public function interpreterInit()
    {
        parent::interpreterInit();
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
 
        if(isset($_REQUEST[$interpreterid.$this->name.$this->id.'page']))
            $this->interpreter_page=$_REQUEST[$interpreterid.$this->name.$this->id.'page'];  
        if(isset($_REQUEST[$interpreterid.$this->name.$this->id.'orderby']))
            $this->interpreter_orderby=$_REQUEST[$interpreterid.$this->name.$this->id.'orderby'];
        if(isset($_REQUEST[$interpreterid.$this->name.$this->id.'orderbydirection']))
            $this->interpreter_orderbyDirection=$_REQUEST[$interpreterid.$this->name.$this->id.'orderbydirection'];
        if(isset($_REQUEST[$interpreterid.$this->name.$this->id.'kennzeichen1']))
            $this->interpreter_kennzeichen1=$_REQUEST[$interpreterid.$this->name.$this->id.'kennzeichen1']; 
    }


    public function getInterpreterRequestValueForDb()
    {
        $this->property['allownull'] = true;
        return parent::getInterpreterRequestValueForDb();
    }

    /*
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $s=false;
        if(isset($this->property['datenbankspalte']))
        {
            $hsConfig = getHsConfig();
            $s['col']=$this->property['datenbankspalte'];
            if($this->getInterpreterRequestValue()=='')
                $s['value']='null';
            else
                $s['value']="'".$hsConfig->escapeString($this->getInterpreterRequestValue())."'";
        }
        return $s;
    }
    */
    /*
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $s=false;
        if(isset($this->property['datenbankspalte']))
        {
            $hsConfig = getHsConfig();
            $s['col']=$this->property['datenbankspalte'];
            if($this->getInterpreterRequestValue()=='')
                $s['value']='null';
            else
                $s['value']="'".$hsConfig->escapeString($this->getInterpreterRequestValue())."'";
        }
        return $s;
    }
    */


    public function getInterpreterRender()
    {
        /**
         * @var \hsconfig $hsconfig
         */
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        
        $value="";
        $text="";

        if(parent::getInterpreterIsFirstNew())
        {
            $value = $this->property['standardvalue'];
            $value = $hsconfig->parseSQLString($value);
        }
        else
            $value=parent::getInterpreterRequestValue();

        if($value!="")
        {
            $text = $this->getValue($value);          
        }

        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['readonly']=="1"?'opacity:0.5;':"").' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <input 
                readonly="readonly" 
                id="input'.$this->id.'" 
                type="textbox" 
                name="textbox'.$this->id.'" 
                value="'.$text.'" 
                style="vertical-align:middle; width:'.($this->width-26).'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').'"
                tabindex="'.$this->property['taborder'].'"
            >
            <input data-customerid="'.$this->getCustomerId().'" id="hidden'.$this->id.'" type="hidden" name="'.$this->id.'" value="'.$value.'">
            '.($this->property['readonly']=="1"?'':'
                <span id="span'.$this->id.'" class="ui-icon ui-icon-closethick" style="width:16px; height:'.$this->height.'px; line-height:'.$this->height.'px; cursor:pointer; float:right; ">del.</span>
            ').'
            <input type="hidden" id="'.$interpreterid.$this->name.$this->id.'orderby"          name="'.$interpreterid.$this->name.$this->id.'orderby"          value="'.$this->interpreter_orderby.'">
            <input type="hidden" id="'.$interpreterid.$this->name.$this->id.'orderbydirection" name="'.$interpreterid.$this->name.$this->id.'orderbydirection" value="'.$this->interpreter_orderbyDirection.'">
            <input type="hidden" id="'.$interpreterid.$this->name.$this->id.'page"             name="'.$interpreterid.$this->name.$this->id.'page"             value="'.$this->interpreter_page.'">
            <input type="hidden" id="'.$interpreterid.$this->name.$this->id.'where"            name="'.$interpreterid.$this->name.$this->id.'where"            value="">            
            <input type="hidden" id="'.$interpreterid.$this->name.$this->id.'kennzeichen1"     name="'.$interpreterid.$this->name.$this->id.'kennzeichen1"     value="'.$this->interpreter_kennzeichen1.'">
            
            '.($this->property['readonly']=="1"?'':'
            <!--<script type="text/javascript" src="http://form-serialize.googlecode.com/svn/trunk/serialize-0.2.min.js"></script>-->
            <script type="text/javascript">
            	$(function() {
                    $( "#span'.$this->id.'" ).click(function() { 
                        $( "#input'.$this->id.'" ).val(""); 
                        $( "#hidden'.$this->id.'" ).val(""); 
                    });
            		$( "#input'.$this->id.'" ).focus(function() {
                        ajax'.$interpreterid.$this->name.$this->id.'();
                    });
                    $("#ajaxcontentdialog_close_'.$this->id.'").click(function () {
                        ajaxcontentdialog_close_'.$this->id.'();
                    });               
                });

                function ajaxcontentdialog_open_'.$this->id.'()
                {
                    $("#ajaxcontentdialog_content_'.$this->id.'").html("<div>Loading Data...</div>");
                    $("#ajaxcontentdialog_shadow_'.$this->id.'").css("display","block");
                    $("#ajaxcontentdialog'.$this->id.'").css("display","block");
                }
                function ajaxcontentdialog_close_'.$this->id.'()
                {
                    $("#ajaxcontentdialog'.$this->id.'").css("display","none");
                    $("#ajaxcontentdialog_shadow_'.$this->id.'").css("display","none");
                }
                
                
                function ajax'.$interpreterid.$this->name.$this->id.'()
                {
                    
                    var param = "project='.$hsconfig->getProjectName().'";
                    param += "&elementclass='.$interpreterid.$this->name.'";
                    param += "&elementid='.$this->id.'";
                    param += "&elementfunction=getInterpreterRenderAjax&elementsubfunction=gettable";
                    param += "&'.$interpreterid.$this->name.$this->id.'orderby=" + $("#'.$interpreterid.$this->name.$this->id.'orderby").val();
                    param += "&'.$interpreterid.$this->name.$this->id.'orderbydirection=" + $("#'.$interpreterid.$this->name.$this->id.'orderbydirection").val();
                    param += "&'.$interpreterid.$this->name.$this->id.'page=" + $("#'.$interpreterid.$this->name.$this->id.'page").val();
                    param += "&'.$hsconfig->getInterpreterParameterGet().'";
                    param += "&'.$interpreterid.$this->name.$this->id.'kennzeichen1=" + $("#'.$interpreterid.$this->name.$this->id.'kennzeichen1").val();
                    param += "&" + $("#formular").serialize();
                    
                    ajaxcontentdialog_open_'.$this->id.'();
                    
                    $.ajax({
                        type: "POST",
                        url: "'.$hsconfig->getBaseUrl().'/interpreter_ajax.php",
                        data: param,
                        success: function(data)
                        {
                            $("#ajaxcontentdialog_content_'.$this->id.'").html(data);
                        },
                        complete: function() {
                        }
                    });
                }
                function select'.$interpreterid.$this->name.$this->id.'(index1)
                {
                    $("#ajaxcontentdialog_content_'.$this->id.'").html("<div>Selecting Data...</div>");
                    
                    $( "#hidden'.$this->id.'" ).val(index1); 
                    
                    var param = "project='.$hsconfig->getProjectName().'";
                    param += "&elementclass='.$interpreterid.$this->name.'";
                    param+="&elementid='.$this->id.'";
                    param+="&elementfunction=getInterpreterRenderAjax&elementsubfunction=getvalue&currentvalue=" + index1;
                    param+="&'.$hsconfig->getInterpreterParameterGet().'";

                    $.ajax({
                        type: "POST",
                        url: "'.$hsconfig->getBaseUrl().'/interpreter_ajax.php",
                        data: param,
                        dataType: "html",
                        success: function(data)
                        {
                            $( "#input'.$this->id.'" ).val(data);
                        },
                        complete: function() {
                            ajaxcontentdialog_close_'.$this->id.'();
                        }
                    });
                }
                function search'.$interpreterid.$this->name.$this->id.'()
                {
                    $(\'#'.$interpreterid.$this->name.$this->id.'where\').val("1");
                    $(\'#'.$interpreterid.$this->name.$this->id.'page\').val("0");
                    ajax'.$interpreterid.$this->name.$this->id.'();
                }
            </script>
            ').'
            
            <div id="ajaxcontentdialog_shadow_'.$this->id.'" style="display:none; position:fixed; left:0px; top:0px; bottom:0px; right:0px; opacity:0.5; background-color:black; z-index:100000; "></div>
            <div id="ajaxcontentdialog'.$this->id.'" style="display:none; background-color:white; border:1px solid #e7e7e7; border-radius:10px 10px 0px 0px; position:fixed; left:10%; top:10%; right:10%; bottom:10%; z-index:100001; ">
                <div style="position:relative; height:25px; background-color:#0078ae; border-radius:10px 10px 0px 0px; " class="ui-widget-header">
                    <div style="text-align:center; line-height:12px; font-weight:bold; color:white; padding:7px 0px; margin:auto; ">'.$this->property['dialogtitle'].'</div>
                    <div id="ajaxcontentdialog_close_'.$this->id.'" style="position:absolute; right:0px; top:0px; color:white; padding:7px; line-height:12px; cursor:pointer; font-weight:bold; border:0px solid #e7e7e7;" >X</div>
                </div>
                <div style="position:relative; box-sizing:border-box; height:calc(100% - 25px); "><div id="ajaxcontentdialog_content_'.$this->id.'" style="padding:20px; height:calc(100% - 40px); overflow:scroll; "></div></div>
            </div>
            
        </div>';
        return $e;
    }
    
	public function getInterpreterRenderAjax()
	{
        if($_REQUEST['elementsubfunction']=='gettable')
        {
            return $this->getTable_ajax();
        }
        else
        {
            return $this->getValue_ajax();
        }
	}
    protected function getValue_ajax()
    {
        return $this->getValue($_REQUEST['currentvalue']);
    }
    protected function getValue($currentvalue)
    {
        
        $hsconfig=getHsConfig();
        $sqlstring=$this->property['sqlstringdisplay'];
        $sqlstring=str_replace("#CURRENTVALUE#",$currentvalue,$sqlstring);
        $sqlstring=$hsconfig->parseSQLString($sqlstring);
        
        if($this->property['debugmode']=="1")
        {
            echo '<div><b>Display:</b><br>'.htmlentities($sqlstring).'</div>';
            //die("");
        }
        
        return $hsconfig->getScalar($sqlstring);
    }
    
    
    protected function getTable_ajax()
    {
        $hsconfig      = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();

        $limitoffset = $this->property['limitoffset'];
        if ($limitoffset == "" || is_numeric($limitoffset) == false) {
            $limitoffset = 50;
        }
        $wherefixed = $this->property['wherefixed'];
        //$orderbyfixed=$this->property['orderbyfixed'];
        $sqlstring      = $this->property['sqlstring'];
        $sqlstringcount = $this->property['sqlstringcount'];
        $wheresearch    = trim($this->property['wheresearch']);
        $ipage          = (int)$this->interpreter_page;
        $limit          = " LIMIT " . ($ipage * $limitoffset) . ", " . $limitoffset . " ";

        $kennzeichenhtml="";
        if($this->property['showkennzeichen1']=="1")
        {
            $kennzeichenhtml="<tr><td align='right'>".$this->property['kennzeichen1title'].":</td><td>";    

            $kennzeichen1sqlstring=$this->property['kennzeichen1sqlstring'];
            //$rs=mysql_query($kennzeichen1sqlstring,$hsconfig->getDbId());
            $rs = $hsconfig->execute($kennzeichen1sqlstring);
            $kennzeichenhtml.='<select style="border:1px solid #dddddd; width:250px; '.$this->property['kennzeichen1style'].' "  onchange="
            $(\'#'.$interpreterid.$this->name.$this->id.'kennzeichen1\').val($(this).val()); 
            $(\'#'.$interpreterid.$this->name.$this->id.'orderby\').val(\'\');
            $(\'#'.$interpreterid.$this->name.$this->id.'orderbydirection\').val(\'\');
            $(\'#'.$interpreterid.$this->name.$this->id.'page\').val(\'\');
            $(\'#'.$interpreterid.$this->name.$this->id.'where\').val(\'\');
            ajax'.$interpreterid.$this->name.$this->id.'(); "
            id="select'.$interpreterid.$this->name.$this->id.'kennzeichen1">';
            if($rs)
            {
                $first=true;
                while($row = $rs->fetch_array(MYSQLI_NUM))
                {
                    $kennzeichenhtml.='<option value="'.$row[0].'"';
                    
                    if($first && $this->interpreter_kennzeichen1=="")
                    {
                        $first=false;
                        $this->interpreter_kennzeichen1=$row[0];
                    }
                    
                    if($this->interpreter_kennzeichen1==$row[0])
                        $kennzeichenhtml.=' selected ';
                    
                    $kennzeichenhtml.='>'.$row[1].'</option>';
                }
                $hsconfig->close($rs);
            }
            $kennzeichenhtml.="</select>
            </td>
            ";            
            $kennzeichenhtml.="</tr>";
        }






        $hasusesearch=false;
        $where=trim($wherefixed);
        //echo $hsconfig->getIndex1Value()."hallo";
        //$where=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$where);
        //$where=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$where);
        if($where=="")
            $where="1";
        $wherehtml="";
        if(trim($wheresearch)!="")
        { 
            $wherehtml="<table>";
            $wherehtml.="";
            $wherefields=explode("||",$wheresearch);
            for($x=0;$x<count($wherefields);$x++)
            {
                $wherefield=$wherefields[$x];
                if($wherefield!="")
                {
                    $items=explode("|",$wherefield);
                    $sqlname=$items[0];
                    $element=$items[1];
                    $displayname=$items[2];
                     
                    
                    $wherehtml.="<tr>";
                    $wherehtml.="<td align='right'>".$displayname.":</td>";
                    $wherehtml.="<td>";
                    
                    if($element=="textbox")
                    {
                        if($_REQUEST[$interpreterid.$this->name.$this->id.'where']=='1')
                        {
                            $_SESSION[$interpreterid.$this->name.$this->id.'where'.$x]=$_REQUEST[$interpreterid.$this->name.$this->id.'where'.$x];
                        }
                        $value=$_SESSION[$interpreterid.$this->name.$this->id.'where'.$x];
                        
                        if($value!="")
                        {
                            $where.=" and ".$sqlname." like '%".str_replace('*','%',$hsconfig->escapeString(stripslashes($value)))."%' ";
                            $hasusesearch=true;
                        }

                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$interpreterid.$this->name.$this->id.'where'.$x."' name='".$interpreterid.$this->name.$this->id.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$interpreterid.$this->name.$this->id.'where'.$x."').keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$interpreterid.$this->name.$this->id."();
                              }
                            });
                        </script>";
                    }
                    elseif($element=="datebox")
                    {
                        if($_REQUEST[$interpreterid.$this->name.$this->id.'where']=='1')
                        {
                            $_SESSION[$interpreterid.$this->name.$this->id.'where'.$x]=$_REQUEST[$interpreterid.$this->name.$this->id.'where'.$x];
                        }
                        $value=$_SESSION[$interpreterid.$this->name.$this->id.'where'.$x];
                        
                        if($value!="")
                        {
                            $where.=" and ".$sqlname." = '".$hsconfig->escapeString(stripslashes($value))."' ";
                            $hasusesearch=true;
                        }

                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$interpreterid.$this->name.$this->id.'where'.$x."' name='".$interpreterid.$this->name.$this->id.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$interpreterid.$this->name.$this->id.'where'.$x."')
                            .datepicker({ dateFormat:'yy-mm-dd' })
                            .keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$interpreterid.$this->name.$this->id."();
                              }
                            });
                        </script>";
                    }
                    elseif($element=="dateboxmin")
                    {
                        if($_REQUEST[$interpreterid.$this->name.$this->id.'where']=='1')
                        {
                            $_SESSION[$interpreterid.$this->name.$this->id.'where'.$x]=$_REQUEST[$interpreterid.$this->name.$this->id.'where'.$x];
                        }
                        $value=$_SESSION[$interpreterid.$this->name.$this->id.'where'.$x];
                        
                        if($value!="")
                        {
                            $where.=" and ".$sqlname." > '".$hsconfig->escapeString(stripslashes($value))." 00:00:00' ";
                            $hasusesearch=true;
                        }

                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$interpreterid.$this->name.$this->id.'where'.$x."' name='".$interpreterid.$this->name.$this->id.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$interpreterid.$this->name.$this->id.'where'.$x."')
                            .datepicker({ dateFormat:'yy-mm-dd' })
                            .keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$interpreterid.$this->name.$this->id."();
                              }
                            });
                        </script>";
                    }
                    elseif($element=="dateboxmax")
                    {
                        if($_REQUEST[$interpreterid.$this->name.$this->id.'where']=='1')
                        {
                            $_SESSION[$interpreterid.$this->name.$this->id.'where'.$x]=$_REQUEST[$interpreterid.$this->name.$this->id.'where'.$x];
                        }
                        $value=$_SESSION[$interpreterid.$this->name.$this->id.'where'.$x];
                        
                        if($value!="")
                        {
                            $where.=" and ".$sqlname." < '".$hsconfig->escapeString(stripslashes($value))." 23:59:59' ";
                            $hasusesearch=true;
                        }

                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$interpreterid.$this->name.$this->id.'where'.$x."' name='".$interpreterid.$this->name.$this->id.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$interpreterid.$this->name.$this->id.'where'.$x."')
                            .datepicker({ dateFormat:'yy-mm-dd' })
                            .keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$interpreterid.$this->name.$this->id."();
                              }
                            });
                        </script>";
                    }
                    elseif(substr($element,0,strlen('selectboxdb::'))=="selectboxdb::")
                    {
                        if($_REQUEST[$interpreterid.$this->name.$this->id.'where']=='1')
                        {
                            $_SESSION[$interpreterid.$this->name.$this->id.'where'.$x]=$_REQUEST[$interpreterid.$this->name.$this->id.'where'.$x];
                        }
                        $value=$_SESSION[$interpreterid.$this->name.$this->id.'where'.$x];
                        
                        if($value!="")
                        {
                            $where.=" and ".$sqlname." like '".stripslashes($hsconfig->escapeString($value))."' ";
                            $hasusesearch=true;
                        }
                        $wherehtml.="<select style='border:1px solid #dddddd; width:200px; ' id='".$interpreterid.$this->name.$this->id.'where'.$x."' name='".$interpreterid.$this->name.$this->id.'where'.$x."'>
                            <option value=''>Bitte w&auml;hlen</option>
                            ";
                            
                        $tmpsql=str_replace('selectboxdb::','',$element);
                        $rs=$hsconfig->execute($tmpsql);
                        if($rs)
                        {
                            while($row = $rs->fetch_array(MYSQLI_NUM))
                            {
                                $wherehtml.='<option value="'.$row[0].'"';
                                
                                if($value==$row[0])
                                    $wherehtml.=' selected ';
                                
                                $wherehtml.='>'.$row[1].'</option>';
                            }
                            $hsconfig->close($rs);
                        }
                        
                        $wherehtml.="</select>
                        <script type='text/javascript'>
                            $('#".$interpreterid.$this->name.$this->id.'where'.$x."').change(function() {
                                search".$interpreterid.$this->name.$this->id."();
                            });
                        </script>";
                    }
                    elseif($element=="custom")
                    {
                        if($_REQUEST[$interpreterid.$this->name.$this->id.'where']=='1')
                        {
                            $_SESSION[$interpreterid.$this->name.$this->id.'where'.$x]=$_REQUEST[$interpreterid.$this->name.$this->id.'where'.$x];
                        }
                        $value=$_SESSION[$interpreterid.$this->name.$this->id.'where'.$x];
                        
                        if($value!="")
                        {
                            $hasusesearch=true;
                            $where.=" and (".str_replace('#VALUE#',stripslashes($hsconfig->escapeString($value)),$sqlname).") ";
                        }
                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$interpreterid.$this->name.$this->id.'where'.$x."' name='".$interpreterid.$this->name.$this->id.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$interpreterid.$this->name.$this->id.'where'.$x."').keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$interpreterid.$this->name.$this->id."();
                              }
                            });
                        </script>";
                        
                    }
                    
                    $wherehtml.="</td>";
                    $wherehtml.="</tr>";
                }
            }
            $wherehtml.="<tr><td></td><td><button type='button' id='".$interpreterid.$this->name.$this->id."wherebutton' onclick='search".$interpreterid.$this->name.$this->id."()'><span class='ui-icon ui-icon-search'></span></button></td></tr>";
            $wherehtml.="</table>
            <script type='text/javascript'> 
                $('#".$interpreterid.$this->name.$this->id."wherebutton').button(); 
            </script>";
        }
        
        if($where!="")
        {
            $where=" WHERE ".$where." ";
        }
        
        $orderby="";
        //if(trim($orderbyfixed)!="")
        //  $orderby=$orderbyfixed;
        
        if($this->interpreter_orderby=="" && $this->property['orderby']!="")
        {
            $this->interpreter_orderby=$this->property['orderby'];
            $this->interpreter_orderbyDirection=$this->property['orderbydirection'];
        }
        if($this->interpreter_orderby!="")
        {
            if($orderby!="")
                $orderby.=",";
            
            $tmp=$this->interpreter_orderby;
            if(strpos($this->interpreter_orderby,".")!==false && strpos($this->interpreter_orderby," ")===false)
            {}
            else
            {
                $tmp="`".$tmp."`";
                //$tmp="'".$tmp."'";
            }
                
                
            $orderby.=$tmp." ".$this->interpreter_orderbyDirection;
        }
        
        if(trim($this->property['orderbyfixed'])!="")
            $orderby=$this->property['orderbyfixed'].($orderby!=""?',':'').$orderby;
        
        if($orderby!="")
            $orderby=" ORDER BY ".$orderby." ";
        $sqlstring=str_replace('#LIMIT#',$limit,$sqlstring);
        $sqlstring=str_replace('#WHERE#',$where,$sqlstring);
        $sqlstring=str_replace('#ORDERBY#',$orderby,$sqlstring);
        $sqlstring=str_replace('#CURRENTKENNZEICHEN1#',$this->interpreter_kennzeichen1,$sqlstring);        
        $sqlstring=$hsconfig->parseSQLString($sqlstring);

        
        //echo $sqlstring;
        $sqlstringcount=str_replace('#LIMIT#',"",$sqlstringcount);
        $sqlstringcount=str_replace('#WHERE#',$where,$sqlstringcount);
        $sqlstringcount=str_replace('#ORDERBY#',$orderby,$sqlstringcount);
        $sqlstringcount=str_replace('#CURRENTKENNZEICHEN1#',$this->interpreter_kennzeichen1,$sqlstringcount);
        $sqlstringcount=$hsconfig->parseSQLString($sqlstringcount);


        $oelements=$this->interpreterGetElements();
        if($oelements!=null)
        {
            foreach($oelements as $oe)
            {
                if($oe->getCustomerId()!="")
                {
                    $id = "#ELEMENT.".$oe->getCustomerId()."#";
                    $v = trim($oe->getInterpreterRequestValue());
                    //echo $id."=".$v."<br>";
                    $sqlstring=str_replace($id,$v,$sqlstring);
                    $sqlstringcount=str_replace($id,$v,$sqlstringcount);
                }
            }
        }


        $rs = null;
        $rowcount = 0;
        $forzeZeroResults = $this->property['onlydisplayafterwhere'] == "1" && !$hasusesearch;
        if(!$forzeZeroResults)
        {
            $rs = $hsconfig->execute($sqlstring);
            $rowcount = $hsconfig->getScalar($sqlstringcount);
        }

        if($rowcount=="")
            $rowcount=0;

        //echo $rowcount;
        $sitecount=ceil($rowcount/$limitoffset);
        $sitehtml="";
        if($sitecount>1)
        {
            $sitehtml='<select style="border:1px solid #dddddd; width:150px; " onchange="
            $(\'#'.$interpreterid.$this->name.$this->id.'page\').val($(this).val());  
            ajax'.$interpreterid.$this->name.$this->id.'(); 
            ">';
            for($x=0;$x<$sitecount;$x++)
            {
                $sitehtml.='<option value="'.$x.'" '.($this->interpreter_page==$x?'selected':'').'>'.($x+1).'</option>';
            }
            $sitehtml.='</select>';
        }
        
        
        
        $html='<div>';
        
        if($this->property['debugmode']=="1")
        {
            $html.='<div><b>Kennzeichen:</b><br>'.htmlentities($this->property['kennzeichen1sqlstring']).'</div>';
            $html.='<div><b>Count:</b><br>'.htmlentities($sqlstringcount).'</div>';
            $html.='<div><b>Select:</b><br>'.htmlentities($sqlstring).'</div>';
        }
        
        $html.='<div style="margin-bottom:5px; ">';
        
        if($kennzeichenhtml!="" || $sitehtml!="")
        {
            $html.='<div style="float:left;">';
            $html.='<table>';
            if($kennzeichenhtml!="")
                $html.='<tr><td align="right">'.$kennzeichenhtml.'</td></tr>';
            if($sitehtml!="")
                $html.='<tr><td align="right">Página:</td><td>'.$sitehtml.'</td></tr>';
            $html.='</table>';
            $html.='</div>';
        }
        if($wherehtml!="")
        {
            $html.='<div style="float:right; " id="wherebox'.$interpreterid.$this->name.$this->id.'">'.$wherehtml.'</div>';
        }
        
        $html.='<div style="clear:both; "></div>
        </div>';
        
        
        $html.='<div>
        <span style="float:right; cursor:pointer; " class="ui-state-default" title="Add new">
            <span class="ui-icon ui-icon-refresh" 
                onclick="
                ajax'.$interpreterid.$this->name.$this->id.'(); 
                "></span>
        </span>
        <div style="clear:both; "></div></div>
        ';


        $html.='</div>';
        
        $colwidth=$this->property['colwidth'];
        $colwidth=explode("|",$colwidth);
        
        //echo $sqlstring;
        //$rs=mysql_query($sqlstring,$hsconfig->getDbId());
        $html.='<table style="width:100%; " cellspacing="0" cellpadding="3" class="tablecontrol">';

        if(!$rs || $rs->num_rows==0)
        {
            $html.='<tr class="ui-state-default">';
            $html.='<td style="color:black; text-align:center; ">';
            if($forzeZeroResults)
                $html.='Use search first';
            else
                $html.='No data found';
            $html.='</td>';
            $html.='</tr>';
        }
        else
        {
            $html.='<tr><th colspan="'.$rs->field_count.'" style="text-align:center;" >Count: '.$rowcount.'</th></tr>';

            $html.='<tr class="ui-widget-header">';
            for($col=1;$col<$rs->field_count;$col++)
            {
                $html.='<th nowrap="nowrap" style="';
                if(isset($colwidth[$col-1]) && $colwidth[$col-1]!="")
                    $html.='width:'.$colwidth[$col-1].'px; ';
                $html.='">
            '.$rs->fetch_field_direct($col)->name.'<br>
            <span style="float:right; cursor:pointer; " class="ui-state-default '.($this->interpreter_orderby==$rs->fetch_field_direct($col)->name && $this->interpreter_orderbyDirection=='ASC'?'ui-state-hover':'').'" 
            onclick="
            $(\'#'.$interpreterid.$this->name.$this->id.'orderby\').val(\''.$rs->fetch_field_direct($col)->name.'\'); 
            $(\'#'.$interpreterid.$this->name.$this->id.'orderbydirection\').val(\'ASC\');  
            ajax'.$interpreterid.$this->name.$this->id.'(); 
            ">
            <span class="ui-icon ui-icon-triangle-1-s "></span>
            </span>
            <span style="float:right; cursor:pointer; " class="ui-state-default '.($this->interpreter_orderby==$rs->fetch_field_direct($col)->name && $this->interpreter_orderbyDirection=='DESC'?'ui-state-hover':'').'" 
            onclick="
            $(\'#'.$interpreterid.$this->name.$this->id.'orderby\').val(\''.$rs->fetch_field_direct($col)->name.'\'); 
            $(\'#'.$interpreterid.$this->name.$this->id.'orderbydirection\').val(\'DESC\'); 
            ajax'.$interpreterid.$this->name.$this->id.'(); 
            ">
            <span class="ui-icon ui-icon-triangle-1-n "></span>
            </span>
            <div style="clear:both; "></div>
            </th>';
            }

            $html.='<th></th>';
            $html.='</tr>';

            while($row = $rs->fetch_array(MYSQLI_NUM))
            {
                //color
                $color="";
                if($this->property['showcolor']=="1" && $this->property['colorsql']!="")
                {
                    $sql=$this->property['colorsql'];
                    $sql=str_replace('#INDEX1#',$row[0],$sql);
                    if($this->property['debugmode']=="1")
                    {
                        echo "COLOR ".$row[0].":<br>";
                        echo $sql."<br>";
                    }
                    $color=trim($hsconfig->getScalar($sql));
                }
                if($color=="")
                    $color="black";
                if($color!="")
                    $color=" color:".$color."; ";

                $html.='<tr style="cursor:pointer; " ';

                if(($row & 1)==1)
                    $html.='class="ui-state-default roweven" ';
                else
                    $html.='class="ui-state-default rowodd" ';

                $html.='>';
                for($col=1;$col<$rs->field_count;$col++)
                {
                    $stype=$rs->fetch_field_direct($col)->type;
                    $html.='<td onclick="select'.$interpreterid.$this->name.$this->id.'(\''.$row[0].'\'); " style="vertical-align:top; '.($stype=='int'||$stype=='real'?'text-align:right; ':'').($stype=='tinyint'?'text-align:center; ':'').' '.$color.'">';
                    $html.=$row[$col];
                    $html.='</td>';
                }

                $html.='<td style="vertical-align:top; width:20px; text-align:center; ">
                    <span style="float:left; cursor:pointer; padding:0 5px; " class="ui-state-default" title="Select"
                    onclick="select'.$interpreterid.$this->name.$this->id.'(\''.$row[0].'\'); ">
                        select
                    </span>
                    <div style="clear:both; "></div>
                ';
                $html.='</td></tr>';
            }
            $hsconfig->close($rs);
        }


        
        $html.='</table>
        <div style="clear:both; "></div>
        </div>
        ';
        return $html;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        //$html.=parent::getEditorProperty_Textbox("Standardtext",'standardtext');
        
        $html.=parent::getEditorProperty_Textarea('SQLString for display data in the textbox. 
        It only return one value. (Variables: #INDEX1#, #INDEX2#, #KENNZEICHEN1#, #CURRENTVALUE#)','sqlstringdisplay');

        $html.=parent::getEditorProperty_Textbox("When the form start in new mode, a standard value should display here (Variables: #INDEX1#, #INDEX2#, #KENNZEICHEN1#, #CURRENTVALUE#)",'standardvalue','');
        $html.=parent::getEditorProperty_Line();
        
        $html.=parent::getEditorProperty_Textbox("Dialog title",'dialogtitle','Dialog');
        $html.=parent::getEditorProperty_Line();

        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();

        $html.=parent::getEditorProperty_Checkbox("Should the flag-dropbox displayed?",'showkennzeichen1','0');
        $html.=parent::getEditorProperty_Textbox("Title from the flag-dropbox",'kennzeichen1title');
        
        $html.=parent::getEditorProperty_Textarea("SQL-statment for the flag-dropbox (first column must be a index 
        and save in the variable #CURRENTKENNZEICHEN1#. Second column gets displayed)",'kennzeichen1sqlstring');
        $html.=parent::getEditorProperty_Textbox("CSS Style for the selectbox",'kennzeichen1style');
        
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("SQL-statment (first column must be a index) (Variables: #WHERE#, #ORDERBY#, #LIMIT#, #ELEMENT.customerid#)  (Tipp: SQL_CALC_FOUND_ROWS)",'sqlstring');
        $html.=parent::getEditorProperty_Textarea("SQL-statment that returns the count from the table (Variables: #WHERE#, #ELEMENT.customerid#) (Tipp: SELECT FOUND_ROWS())",'sqlstringcount');
        $html.=parent::getEditorProperty_Textbox("Rows, that gets displayed in the grid",'limitoffset','30');
        $html.=parent::getEditorProperty_Textarea("Where-condition (Variables: #CURRENTKENNZEICHEN1#, #INDEX1#, #INDEX2#, #KENNZEICHEN1#, #ELEMENT.customerid#)",'wherefixed');
        $html.=parent::getEditorProperty_Textbox("Columnname and orderdirection from the permanent sortorder",'orderbyfixed');
        $html.=parent::getEditorProperty_Textbox("Columnname from the standardorder that can changed by the user",'orderby');
        $html.=parent::getEditorProperty_Selectbox("Direction from the standardorder that can changed by the user",'orderbydirection',array('ASC'=>'ASC','DESC'=>'DESC'),'ASC');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("Search (Schema: SQL-COLUMNNAME|TYPE|DISPLAYNAME-NAME||SQL-SPALTENNAME|TYP|ANZEIGE-NAME e.g. mytable.columnname|textbox|displayname||mytable.columnname2|selectboxdb::select id,value from anothertable|displayname2||index1 = (select findex1 from table where searchcolumn='#VALUE#')|custom|displayname3  Type:textbox, selectboxdb, datebox, dateboxmin, dateboxmax, custom)",'wheresearch');
        $html.=parent::getEditorProperty_Checkbox("Display data only after user had searched", 'onlydisplayafterwhere', '0');
        $html.=parent::getEditorProperty_Line();
        
        $html.=parent::getEditorProperty_Checkbox("Use Row text color?",'showcolor','0');
        $html.=parent::getEditorProperty_Textarea("Every row executes following sql statement. The retunvalue must be a colorvalue in CSS. Variabes: #INDEX1#",'colorsql');
        
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Columnwidth in px with | separated",'colwidth');

        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-Modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            true,
            true
        );
        return $html;
    }
    
	function getSQL($table)
	{
        $dbfield=$this->property['datenbankspalte'];
		if(trim($dbfield)=="" || $table=="")
			return "";
		return "alter table `".$table."` add column `".$dbfield."` VARCHAR(250) DEFAULT NULL; ";
	}
}

?>
