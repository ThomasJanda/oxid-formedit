<?php
include_once(__DIR__ . '/table.php');

class tabletree extends table
{
    var $name="tabletree";
    var $editorname="Grid Tree";
    var $editordescription='Shows a Grid with subrows.';

    protected function getTable()
    {
        $hsconfig=getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        
        $formularid_edit=$this->property['formularid_edit'];
        $formularid_new=$this->property['formularid_new'];
        $limitoffset=$this->property['limitoffset'];
        if($limitoffset=="" || is_numeric($limitoffset)==false)
        {
            $limitoffset=50;
        }
        $wherefixed=$this->property['wherefixed'];
        //$orderbyfixed=$this->property['orderbyfixed'];
        $sqlstring=$this->property['sqlstring'];
        $sqlstringcount=$this->property['sqlstringcount'];
        $wheresearch=trim($this->property['wheresearch']);
        $exportcsv=$this->property['exportcsv'];
        $ipage=$this->interpreter_page;
        $limit=" LIMIT ".($ipage*$limitoffset).", ".$limitoffset." ";
        


        
        
        
        
        
        
        $where=trim($wherefixed);
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
                        if($_REQUEST[$this->uniqueGridId.'where']=='1')
                        {
                            $_SESSION[$this->uniqueGridId.'where'.$x]=$_REQUEST[$this->uniqueGridId.'where'.$x];
                        }
                        $value=$_SESSION[$this->uniqueGridId.'where'.$x];
                        
                        if($value!="")
                            $where.=" and ".$sqlname." like '%".str_replace('*','%',$hsconfig->escapeString(stripslashes($value)))."%' ";
                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$this->uniqueGridId.'where'.$x."' name='".$this->uniqueGridId.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$this->uniqueGridId.'where'.$x."').keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$this->uniqueGridId."();
                              }
                            });
                        </script>";
                    }
                    elseif($element=="datebox")
                    {
                        if($_REQUEST[$this->uniqueGridId.'where']=='1')
                        {
                            $_SESSION[$this->uniqueGridId.'where'.$x]=$_REQUEST[$this->uniqueGridId.'where'.$x];
                        }
                        $value=$_SESSION[$this->uniqueGridId.'where'.$x];
                        
                        if($value!="")
                            $where.=" and ".$sqlname." = '".$hsconfig->escapeString(stripslashes($value))."' ";
                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$this->uniqueGridId.'where'.$x."' name='".$this->uniqueGridId.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$this->uniqueGridId.'where'.$x."')
                            .datepicker({ dateFormat:'yy-mm-dd' })
                            .keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$this->uniqueGridId."();
                              }
                            });
                        </script>";
                    }
                    elseif($element=="dateboxmin")
                    {
                        if($_REQUEST[$this->uniqueGridId.'where']=='1')
                        {
                            $_SESSION[$this->uniqueGridId.'where'.$x]=$_REQUEST[$this->uniqueGridId.'where'.$x];
                        }
                        $value=$_SESSION[$this->uniqueGridId.'where'.$x];
                        
                        if($value!="")
                            $where.=" and ".$sqlname." > '".$hsconfig->escapeString(stripslashes($value))." 00:00:00' ";
                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$this->uniqueGridId.'where'.$x."' name='".$this->uniqueGridId.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$this->uniqueGridId.'where'.$x."')
                            .datepicker({ dateFormat:'yy-mm-dd' })
                            .keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$this->uniqueGridId."();
                              }
                            });
                        </script>";
                    }
                    elseif($element=="dateboxmax")
                    {
                        if($_REQUEST[$this->uniqueGridId.'where']=='1')
                        {
                            $_SESSION[$this->uniqueGridId.'where'.$x]=$_REQUEST[$this->uniqueGridId.'where'.$x];
                        }
                        $value=$_SESSION[$this->uniqueGridId.'where'.$x];
                        
                        if($value!="")
                            $where.=" and ".$sqlname." < '".$hsconfig->escapeString(stripslashes($value))." 23:59:59' ";
                        $wherehtml.="<input style='border:1px solid #dddddd; width:200px; ' type='textbox' id='".$this->uniqueGridId.'where'.$x."' name='".$this->uniqueGridId.'where'.$x."' value='".$value."'>
                        <script type='text/javascript'>
                            $('#".$this->uniqueGridId.'where'.$x."')
                            .datepicker({ dateFormat:'yy-mm-dd' })
                            .keypress(function(event) {
                              if ( event.which == 13 ) 
                              {
                                 search".$this->uniqueGridId."();
                              }
                            });
                        </script>";
                    }
                    elseif(substr($element,0,strlen('selectboxdb::'))=="selectboxdb::")
                    {
                        if($_REQUEST[$this->uniqueGridId.'where']=='1')
                        {
                            $_SESSION[$this->uniqueGridId.'where'.$x]=$_REQUEST[$this->uniqueGridId.'where'.$x];
                        }
                        $value=$_SESSION[$this->uniqueGridId.'where'.$x];
                        
                        if($value!="")
                            $where.=" and ".$sqlname." like '".stripslashes($hsconfig->escapeString($value))."' ";
                        $wherehtml.="<select style='border:1px solid #dddddd; width:200px; ' id='".$this->uniqueGridId.'where'.$x."' name='".$this->uniqueGridId.'where'.$x."'>
                            <option value=''>Bitte w&auml;hlen</option>
                            ";
                            
                        $tmpsql=str_replace('selectboxdb::','',$element);
                        //$rs=mysql_query($tmpsql,$hsconfig->getDbId());
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
                            $('#".$this->uniqueGridId.'where'.$x."').change(function() {
                                search".$this->uniqueGridId."();
                            });
                        </script>";
                    }
                    
                    $wherehtml.="</td>";
                    $wherehtml.="</tr>";
                }
            }
            $wherehtml.="<tr><td></td><td><button type='button' id='".$this->uniqueGridId."wherebutton' onclick='search".$this->uniqueGridId."()'><span class='ui-icon ui-icon-search'></span></button></td></tr>";
            $wherehtml.="</table>
            <script type='text/javascript'> 
                $('#".$this->uniqueGridId."wherebutton').button(); 
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

        $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
        $sqlstring=str_replace('#INDEX2#',$this->interpreter_index2,$sqlstring);
        $sqlstring=str_replace('#KENNZEICHEN1#',$this->interpreter_kennzeichen1,$sqlstring);        
        $sqlstring=$hsconfig->parseSQLString($sqlstring);

        
        //echo $sqlstring;
        $sqlstringcount=str_replace('#LIMIT#',"",$sqlstringcount);
        $sqlstringcount=str_replace('#WHERE#',$where,$sqlstringcount);
        $sqlstringcount=str_replace('#ORDERBY#',$orderby,$sqlstringcount);
        $sqlstringcount=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstringcount);
        $sqlstringcount=str_replace('#INDEX2#',$this->interpreter_index2,$sqlstringcount);
        $sqlstringcount=str_replace('#KENNZEICHEN1#',$this->interpreter_kennzeichen1,$sqlstringcount);
        $sqlstringcount=$hsconfig->parseSQLString($sqlstringcount);

        //NEWRELIC: MysqlError: Query was empty
        $rowcount=0;
        if(trim($sqlstringcount)!="")
            $rowcount=$hsconfig->getScalar($sqlstringcount);
        if($rowcount=="")
            $rowcount=0;

        //echo $rowcount;
        $sitecount=ceil($rowcount/$limitoffset);
        $sitehtml="";
        if($sitecount>1)
        {
            $sitehtml='<select style="border:1px solid #dddddd; width:150px; " onchange="
            $(\'#'.$this->uniqueGridId.'page\').val($(this).val());  
            ajax'.$this->uniqueGridId.'(); 
            ">';
            for($x=0;$x<$sitecount;$x++)
            {
                $sitehtml.='<option value="'.$x.'" '.($this->interpreter_page==$x?'selected':'').'>'.($x+1).'</option>';
            }
            $sitehtml.='</select>';
        }
        
        
        
        $html='<div data-hasparentcontrol="'.$this->getParentControl().'" style="'.$this->getParentControlCss().'">';
        
        if($this->property['debugmode']=="1")
        {
            $html.='<div><b>Kennzeichen:</b><br>'.htmlentities($this->property['kennzeichen1sqlstring']).'</div>';
            $html.='<div><b>Count:</b><br>'.htmlentities($sqlstringcount).'</div>';
            $html.='<div><b>Select:</b><br>'.htmlentities($sqlstring).'</div>';
        }
        
        $html.='<div style="margin-bottom:5px; ">';
        
        if($kennzeichenhtml!="" || $sitehtml!="" || $exportcsv=="1")
        {
            $html.='<div style="float:left;">';
            $html.='<table>';
            /*
            if($kennzeichenhtml!="")
                $html.='<tr><td align="right">'.$kennzeichenhtml.'</td></tr>';
            */
            if($sitehtml!="")
                $html.='<tr><td align="right">Página:</td><td>'.$sitehtml.'</td></tr>';
            if($exportcsv=="1")
            {
                $html.='<tr><td></td><td>
                    <button type="button" title="Export CSV" id="'.$this->uniqueGridId.'exportcsvbutton" onclick="exportcsv'.$this->uniqueGridId.'()"><span class="ui-icon ui-icon-suitcase"></span></button>
                    <script type="text/javascript"> 
                        $("#'.$this->uniqueGridId.'exportcsvbutton").button(); 
                    </script>
                </td></tr>';
            }
            $html.='</table>';
            $html.='</div>';
        }
        if($wherehtml!="")
        {
            $html.='<div style="float:right; ">'.$wherehtml.'</div>';
        }
        
        $html.='<div style="clear:both; "></div>';
        

        $html.='<div>';
        $html.='<span style=float:right;cursor:pointer class="ui-state-default" title="Refresh">
                        <span class="ui-icon ui-icon-refresh" data-table-refresh
                            data-table-id="'.$this->id.'" data-table-unique-id="'.$this->uniqueGridId.'">
                        </span>
                    </span>';


        if($this->property['showkennzeichen1buttonnew']=="1" && $this->property['kennzeichen1formularid_new']!="")
        {
            $tmp = $this->property['kennzeichen1textnewbutton'];
            if($tmp=="")
            {
                $html.='
                <span style="float:left; cursor:pointer; " class="ui-state-default" title="Add new group">
                    <span class="ui-icon ui-icon-circle-plus"
                        onclick="
                        $(\'#index1value\').val(\''.uniqid().'\');
                        $(\'#formularid\').val(\''.$this->property['kennzeichen1formularid_new'].'\');
                        $(\'#kennzeichen1value\').val(\'\')
                        $(\'#navi\').val(\'NEW\');
                        $(\'#formular\').submit();
                        "></span>
                </span>
                ';
            }
            else
            {
                $html.='
                <span style="float:left; cursor:pointer; padding:2px 5px; "
                    class="ui-state-default"
                    onclick="
                    $(\'#index1value\').val(\''.uniqid().'\');
                    $(\'#formularid\').val(\''.$this->property['kennzeichen1formularid_new'].'\');
                    $(\'#kennzeichen1value\').val(\'\')
                    $(\'#navi\').val(\'NEW\');
                    $(\'#formular\').submit();
                    ">'.$tmp.'</span>
                ';
            }

        } 
        $html.='<div style="clear:both; "></div></div>';          

        /*
        if($this->property['showbuttonnew']=='1' && $formularid_new!="")
        {
            $html.='
            <div>
                <label style="width:50px; text-align:right; padding-right:10px; float:left; ">Rows:</label>
                <span style="float:left; cursor:pointer; " class="ui-state-default" title="Add new">
                    <span class="ui-icon ui-icon-circle-plus" 
                        onclick="
                        $(\'#index1value\').val(\''.uniqid().'\'); 
                        $(\'#formularid\').val(\''.$formularid_new.'\'); 
                        $(\'#kennzeichen1value\').val(\''.$this->interpreter_kennzeichen1.'\')
                        $(\'#navi\').val(\'NEW\'); 
                        $(\'#formular\').submit(); 
                        "></span>
                </span>
            </div>
            ';
        }
        */

        $html.='</div>';
        
        $colwidth=$this->property['colwidth'];
        $colwidth=explode("|",$colwidth);
        
        //echo $sqlstring;
        //$rs=mysql_query($sqlstring,$hsconfig->getDbId());
        $rs = $hsconfig->execute($sqlstring);
        $html.='<table style="width:100%; " cellspacing="0" cellpadding="3" class="tablecontrol">';
        
        $html.='<tr><th colspan="'.($rs->field_count).'" style="text-align:center;" >Displaying: '.$rowcount.'</th></tr>';
        
        $html.='<tr class="ui-widget-header">';
        for($col=2;$col<$rs->field_count;$col++)
        {
            $colW = $col - 1;
            $sortFormat="string";

            $width="";
            if(isset($colwidth[$col-1]) && $colwidth[$col-1]!="")
                $width = 'width:'.$colwidth[$col-1].'px; ';

            $headerLabel=$rs->fetch_field_direct($col)->name;

            //do not display sort
            $sDisplay='inline';
            if(trim($this->property['unsortable'])!="")
            {
                $aUnsortable = explode("|",trim($this->property['unsortable']));
                $aUnsortable = array_map('trim',$aUnsortable);
                if(in_array($headerLabel,$aUnsortable))
                    $sDisplay = 'none';
            }

            $ascActive = $this->interpreter_orderby == $headerLabel && $this->interpreter_orderbyDirection == 'ASC' ? 'ui-state-hover' : '';
            $descActive = $this->interpreter_orderby == $headerLabel && $this->interpreter_orderbyDirection == 'DESC' ? 'ui-state-hover' : '';

            $html.=<<<html
            <th nowrap="nowrap" style="$width;$cellStyle">
                $headerLabel<br>
                <span style="float:right;cursor:pointer; display:$sDisplay; "
                    class="ui-state-default $descActive"
                    data-table-order-by="$headerLabel" 
                    data-table-order-by-dir="DESC"
                    data-table-order-by-index="$colW"
                    data-table-order-by-type="$sortFormat"
                    data-table-id="$this->id" 
                    data-table-unique-id="$this->uniqueGridId"
                    >
                    <span class="ui-icon ui-icon-triangle-1-s"></span>
                </span>
                <span style="float:right;cursor:pointer; display:$sDisplay; "
                    class="ui-state-default $ascActive"
                    data-table-order-by$sortType="$headerLabel" 
                    data-table-order-by-dir="ASC"
                    data-table-order-by-index="$colW"
                    data-table-order-by-type="$sortFormat"
                    data-table-id="$this->id" 
                    data-table-unique-id="$this->uniqueGridId">
                    <span class="ui-icon ui-icon-triangle-1-n "></span>
                </span>
                <div style="clear:both; "></div>    
            </th>
html;
            /*
            $html.='<th nowrap="nowrap" style="';
            if(isset($colwidth[$col-1]) && $colwidth[$col-1]!="")
                $html.='width:'.$colwidth[$col-1].'px; ';
            $html.='">
            '.$rs->fetch_field_direct($col)->name.'<br>
            <span style="float:right; cursor:pointer; " class="ui-state-default '.($this->interpreter_orderby==$rs->fetch_field_direct($col)->name && $this->interpreter_orderbyDirection=='ASC'?'ui-state-hover':'').'" 
            onclick="
            $(\'#'.$this->uniqueGridId.'orderby\').val(\''.$rs->fetch_field_direct($col)->name.'\'); 
            $(\'#'.$this->uniqueGridId.'orderbydirection\').val(\'ASC\');  
            ajax'.$this->uniqueGridId.'(); 
            ">
            <span class="ui-icon ui-icon-triangle-1-s "></span>
            </span>
            <span style="float:right; cursor:pointer; " class="ui-state-default '.($this->interpreter_orderby==$rs->fetch_field_direct($col)->name && $this->interpreter_orderbyDirection=='DESC'?'ui-state-hover':'').'" 
            onclick="
            $(\'#'.$this->uniqueGridId.'orderby\').val(\''.$rs->fetch_field_direct($col)->name.'\'); 
            $(\'#'.$this->uniqueGridId.'orderbydirection\').val(\'DESC\'); 
            ajax'.$this->uniqueGridId.'(); 
            ">
            <span class="ui-icon ui-icon-triangle-1-n "></span>
            </span>
            <div style="clear:both; "></div>
            </th>';
            */
        }
        
        if($this->property['showbuttondelete']=='1')
        {
            $html.='<th></th>';    
        }
        
        if(trim($this->property['navigation'])!='')
        {
            $html.='<th></th>';
        } 
        $html.='</tr>';
        


        $columnscount=$rs->field_count-2;
        if($this->property['showbuttondelete']=='1')
        {
            $columnscount++;
        }

        if(trim($this->property['navigation'])!='')
        {
            $columnscount++;
        }

        $lastkennzeichen1=null;
        $kennzeichen1sqlstring=$this->property['kennzeichen1sqlstring'];
        $kennzeichen1sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$kennzeichen1sqlstring);
        $kennzeichen1sqlstring=str_replace('#INDEX2#',$this->interpreter_index2,$kennzeichen1sqlstring);
        $kennzeichen1sqlstring=$hsconfig->parseSQLString($kennzeichen1sqlstring);
        $kennzeichenrs=$hsconfig->execute($kennzeichen1sqlstring);

//echo $kennzeichen1sqlstring;

        $html.='<tr><td class="groupheader" colspan="'.($columnscount).'">';

        $html.='<div style="float:left; margin-right:10px; ">'.($kennzeichenrs->num_rows).' group(s)</div>';

        $html.='<div style="float:left; margin-right:10px; width: calc(101% - 300px); ">';
        $html.='<select style="border:1px solid #dddddd; width:100%; " id="select'.$this->uniqueGridId.'kennzeichen1">';
        if($kennzeichenrs)
        {
            while($kennzeichenrow = $kennzeichenrs->fetch_array(MYSQLI_NUM))
            {
                $html.='<option value="'.$kennzeichenrow[0].'">'.$kennzeichenrow[1].'</option>';
            }
            //$hsconfig->close($kennzeichenrs);
        }
        $html.='</select>';
        $html.='</div>';

        if(($kennzeichenrs->num_rows)>0)
        {
            if($this->property['showbuttonnew']=='1' && $formularid_new!="")
            {

                $tmp = $this->property['textnewbutton'];
                if($tmp=="")
                {
                    $html.='<span style="cursor:pointer; float:left; margin-right:5px; " class="ui-state-default" title="Add new row">
                    <span class="ui-icon ui-icon-circle-plus"
                        onclick="
                        if($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val()!=\'\')
                        {
                            $(\'#index1value\').val(\''.uniqid().'\');
                            $(\'#formularid\').val(\''.$formularid_new.'\');
                            $(\'#kennzeichen1value\').val($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val())
                            $(\'#navi\').val(\'NEW\');
                            $(\'#formular\').submit();
                        }
                        "></span>
                    </span>
                ';
                }
                else
                {
                    $html.='<span style="cursor:pointer; float:left; margin-right:5px; padding:0px 5px; " class="ui-state-default"
                    onclick="
                    if($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val()!=\'\')
                    {
                        $(\'#index1value\').val(\''.uniqid().'\');
                        $(\'#formularid\').val(\''.$formularid_new.'\');
                        $(\'#kennzeichen1value\').val($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val())
                        $(\'#navi\').val(\'NEW\');
                        $(\'#formular\').submit();
                    }
                    ">'.$tmp.'</span>';
                }


            }
            if($this->property['showkennzeichen1buttonedit']=="1" && $this->property['kennzeichen1formularid_edit']!="")
            {
                $tmp=$this->property['kennzeichen1texteditbutton'];
                if($tmp=="")
                {
                    $html .= '<span style="cursor:pointer; float:left; margin-right:5px; " class="ui-state-default" title="Edit group">
                    <span class="ui-icon ui-icon-document" 
                        onclick="
                        if($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val()!=\'\')
                        {
                            $(\'#index1value\').val($(\'#select' . $this->uniqueGridId. 'kennzeichen1\').val());
                            $(\'#formularid\').val(\'' . $this->property['kennzeichen1formularid_edit'] . '\');
                            $(\'#kennzeichen1value\').val(\'\')
                            $(\'#navi\').val(\'EDIT\'); 
                            $(\'#formular\').submit(); 
                        }
                        "></span>
                        </span>';
                }
                else
                {
                    $html .= '<span style="cursor:pointer; float:left; margin-right:5px; padding:0px 5px; " class="ui-state-default"
                    onclick="
                    if($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val()!=\'\')
                    {
                        $(\'#index1value\').val($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val());
                        $(\'#formularid\').val(\'' . $this->property['kennzeichen1formularid_edit'] . '\');
                        $(\'#kennzeichen1value\').val(\'\')
                        $(\'#navi\').val(\'EDIT\');
                        $(\'#formular\').submit();
                    }
                    ">'.$tmp.'</span>';
                }
            }
            if($this->property['showkennzeichen1buttondelete']=="1")
            {
                $tmp = $this->property['kennzeichen1textdeletebutton'];
                if($tmp=="")
                {
                    $html .= '<span style="cursor:pointer; float:left; margin-right:5px; " class="ui-state-default" title="Delete group"
                    onclick="
                    if($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val()!=\'\')
                    {
                        deletekennzeichen1' . $this->uniqueGridId . '($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val());
                    }
                    ">
                    <span class="ui-icon ui-icon-circle-minus"></span>
                    </span>';
                }
                else
                {
                    $html .= '<span style="cursor:pointer; float:left; margin-right:5px; padding:0px 5px; " class="ui-state-default"
                    onclick="
                    if($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val()!=\'\')
                    {
                        deletekennzeichen1' . $this->uniqueGridId . '($(\'#select' . $this->uniqueGridId . 'kennzeichen1\').val());
                    }
                    ">
                    '.$tmp.'
                    </span>';
                }
            }
            if(trim($this->property['kennzeichen1navigation'])!='')
            {
                $navi=trim($this->property['kennzeichen1navigation']);
                $naviitems=explode("||",$navi);

                for($x=0;$x<count($naviitems);$x++)
                {
                    $n=explode("|",$naviitems[$x]);

                    $html.='<span style="float:left; cursor:pointer; padding:0px 3px 0px 3px; margin-right:5px; " class="ui-state-default" title="'.$n[0].'"
                    onclick="
                        if($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val()!=\'\')
                        {
                            if(\''.$n[2].'\'==\'#INDEX1#\')
                                $(\'#index1value\').val($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val());
                            else if(\''.$n[2].'\'==\'#INDEX2#\')
                                $(\'#index2value\').val($(\'#select'.$this->uniqueGridId.'kennzeichen1\').val());
                            $(\'#formularid\').val(\''.$n[1].'\');
                            $(\'#navi\').val(\'EDIT\');
                            $(\'#kennzeichen1value\').val(\'\');
                            if($(\'#formularid\').val()!=\'\')
                                $(\'#formular\').submit();
                        }
                    ">
                    '.$n[0].'
                    </span>
                    ';
                }
            }
        }
        $html.='</td></tr>';

        if($kennzeichenrs && $this->property['kennzeichen1showdesc']=='1')
        {
            $html.='<tr><td id="select'.$this->uniqueGridId.'kennzeichen1desc" class="groupheader" colspan="'.($columnscount).'"  style="font-weight:normal; ">';
            $kennzeichenrs->data_seek(0);
            while($kennzeichenrow = $kennzeichenrs->fetch_array(MYSQLI_NUM))
            {
                $html.='<div id="select'.$this->uniqueGridId.'kennzeichen1desc'.$kennzeichenrow[0].'" style="display:none; ">';

                $sql=$this->property['kennzeichen1descsql'];
                $sql=str_replace('#INDEX1#',$kennzeichenrow[0],$sql);
                $tmp=trim($hsconfig->getScalar($sql));
                if($tmp!="")
                {
                    $html.=$tmp;
                }

                $html.='</div>';
            }
            $html.="<script type='text/javascript'>
                        $('#select".$this->uniqueGridId."kennzeichen1').change(function() {
                            var v = $('#select".$this->uniqueGridId."kennzeichen1').val();
                            $('#select".$this->uniqueGridId."kennzeichen1desc div').css('display','none');
                            $('#select".$this->uniqueGridId."kennzeichen1desc' + v).css('display','block');
                        });
                        $('#select".$this->uniqueGridId."kennzeichen1').change();
                    </script>";
            $html.='</td></tr>';
        }

        if($rs) {
            if(($rs->num_rows)==0)
            {
                $html.='<tr class="ui-state-default">';
                //$html.='<td style="color:black; " colspan="'.(mysql_num_fields($rs)-1).'">Keine Daten gefunden</td>';
                $html.='<td style="color:black; " colspan="'.(($rs->field_count)-1).'"> No data found</td>';
                if($this->property['showbuttondelete']=='1')
                {
                    $html.='<td></td>'; 
                }
                if(trim($this->property['navigation'])!='')
                {
                    $html.='<td></td>';
                } 
                $html.='</tr>';
            }
            else
            {
                $rowCounter = 0;
                while($row = $rs->fetch_array(MYSQLI_NUM))
                {
                    $currentkennzeichen1=trim($row[1]);
                    if($lastkennzeichen1!=$currentkennzeichen1 || $lastkennzeichen1===null)
                    {
                        $html.='<tr><td class="groupheader" style="border-top:2px solid black; " colspan="'.($columnscount).'">';

                        while($kennzeichenrow = $kennzeichenrs->fetch_array(MYSQLI_NUM))
                        {
                            if($currentkennzeichen1==$kennzeichenrow[0])
                            {
                                $html.='<span ';
                                if($this->property['showkennzeichen1buttonedit']=="1" && $this->property['kennzeichen1formularid_edit']!="")
                                {
                                    $html.=' style="cursor:pointer; " onclick="
                                    $(\'#index1value\').val(\''.$currentkennzeichen1.'\'); 
                                    $(\'#formularid\').val(\''.$this->property['kennzeichen1formularid_edit'].'\'); 
                                    $(\'#kennzeichen1value\').val(\'\');
                                    $(\'#navi\').val(\'EDIT\'); 
                                    $(\'#formular\').submit(); 
                                    " ';
                                }
                                $html.='>';
                                
                                $html.=$kennzeichenrow[1];
                                $html.='</span>';
                                break;
                            }
                        }
                        $html.='<div style="float:right; ">';
                           
                           
                        if(trim($this->property['kennzeichen1navigation'])!='')
                        {
                            $navi=trim($this->property['kennzeichen1navigation']);
                            $naviitems=explode("||",$navi);
                            
                            for($x=0;$x<count($naviitems);$x++)
                            {
                                $n=explode("|",$naviitems[$x]);
                                
                                $html.='
                                    <span style="float:right; cursor:pointer; padding:0px 3px 0px 3px; margin-left:5px; " class="ui-state-default" title="'.$n[0].'"
                                    onclick="
                                        if(\''.$n[2].'\'==\'#INDEX1#\')
                                            $(\'#index1value\').val(\''.$currentkennzeichen1.'\');
                                        else if(\''.$n[2].'\'==\'#INDEX2#\')
                                            $(\'#index2value\').val(\''.$currentkennzeichen1.'\');
                                        $(\'#formularid\').val(\''.$n[1].'\');
                                        $(\'#navi\').val(\'EDIT\');
                                        $(\'#kennzeichen1value\').val(\'\');
                                        if($(\'#formularid\').val()!=\'\')
                                            $(\'#formular\').submit();
                                    ">
                                    '.$n[0].'
                                    </span>
                                    ';
                            }
                        } 
                            
                        if($this->property['showkennzeichen1buttondelete']=="1")
                        {
                            $tmp = $this->property['kennzeichen1textdeletebutton'];
                            if($tmp=="")
                            {
                                $html.='
                                <span style="cursor:pointer; float:right; margin-left:5px; " class="ui-state-default" title="Delete group"
                                onclick="
                                deletekennzeichen1'.$this->uniqueGridId.'(\''.$currentkennzeichen1.'\');
                                ">
                                <span class="ui-icon ui-icon-circle-minus"></span>
                                </span>';
                            }
                            else
                            {
                                $html.='
                                <span style="cursor:pointer; float:right; margin-left:5px; padding:0px 5px; " class="ui-state-default"
                                onclick="
                                deletekennzeichen1'.$this->uniqueGridId.'(\''.$currentkennzeichen1.'\');
                                ">'.$tmp.'</span>';
                            }

                        }
                        if($this->property['showkennzeichen1buttonedit']=="1" && $this->property['kennzeichen1formularid_edit']!="")
                        {
                            $tmp=$this->property['kennzeichen1texteditbutton'];
                            if($tmp=="")
                            {
                                $html.='<span style="cursor:pointer; float:right; margin-left:5px; " class="ui-state-default" title="Edit group">
                                <span class="ui-icon ui-icon-document"
                                    onclick="
                                    $(\'#index1value\').val(\''.$currentkennzeichen1.'\');
                                    $(\'#formularid\').val(\''.$this->property['kennzeichen1formularid_edit'].'\');
                                    $(\'#kennzeichen1value\').val(\'\')
                                    $(\'#navi\').val(\'EDIT\');
                                    $(\'#formular\').submit();
                                    "></span>
                                </span>';
                            }
                            else
                            {
                                $html.='<span style="cursor:pointer; float:right; margin-left:5px; padding:0px 5px; "
                                class="ui-state-default"
                                onclick="
                                $(\'#index1value\').val(\''.$currentkennzeichen1.'\');
                                $(\'#formularid\').val(\''.$this->property['kennzeichen1formularid_edit'].'\');
                                $(\'#kennzeichen1value\').val(\'\')
                                $(\'#navi\').val(\'EDIT\');
                                $(\'#formular\').submit();
                                ">'.$tmp.'</span>';
                            }

                        } 
                        if($this->property['showbuttonnew']=='1' && $formularid_new!="")
                        {
                            $tmp = $this->property['textnewbutton'];
                            if($tmp=="")
                            {
                                $html.='<span style="cursor:pointer; float:right; margin-left:5px; " class="ui-state-default" title="Add new row">
                                <span class="ui-icon ui-icon-circle-plus"
                                    onclick="
                                    $(\'#index1value\').val(\''.uniqid().'\');
                                    $(\'#formularid\').val(\''.$formularid_new.'\');
                                    $(\'#kennzeichen1value\').val(\''.$currentkennzeichen1.'\')
                                    $(\'#navi\').val(\'NEW\');
                                    $(\'#formular\').submit();
                                    "></span>
                                </span>
                                ';
                            }
                            else
                            {
                                $html.='<span style="cursor:pointer; float:right; margin-left:5px; padding:0px 5px; "
                                class="ui-state-default"
                                onclick="
                                $(\'#index1value\').val(\''.uniqid().'\');
                                $(\'#formularid\').val(\''.$formularid_new.'\');
                                $(\'#kennzeichen1value\').val(\''.$currentkennzeichen1.'\')
                                $(\'#navi\').val(\'NEW\');
                                $(\'#formular\').submit();
                                ">'.$tmp.'</span>
                                ';
                            }

                        }  
  
                        $html.='</div></td></tr>';
                        
                        if($this->property['kennzeichen1showdesc']=='1')
                        {
                            $sql=$this->property['kennzeichen1descsql'];
                            $sql=str_replace('#INDEX1#',$currentkennzeichen1,$sql);
                            if($this->property['debugmode']=="1")
                            {
                                echo "FLAG ROW DESC ".$currentkennzeichen1.":<br>";
                                echo $sql."<br>";
                            }
                            $tmp=trim($hsconfig->getScalar($sql));
                            if($tmp!="")
                            {
                                $html.='<tr><td class="groupheader" colspan="'.($columnscount).'" style="font-weight:normal; ">';
                                $html.=$tmp;
                                $html.='</td></tr>';
                            }
                        }
                        
                        $lastkennzeichen1=$currentkennzeichen1;
                        
                        
                        
                        
                        $html.='<tr class="ui-widget-header">';
                        for($col=2;$col<$rs->field_count;$col++)
                        {
                            $html.='<th nowrap="nowrap">'.$rs->fetch_field_direct($col)->name.'</th>';
                        }
                        if($this->property['showbuttondelete']=='1')
                        {
                            $html.='<th></th>';    
                        }
                        if(trim($this->property['navigation'])!='')
                        {
                            $html.='<th></th>';
                        } 
                        $html.='</tr>';
                        
                        
                        
                        
                        
                        
                        
                    }
                    
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
                    
                    $html.='<tr style="';
                    if($this->property['showbuttonedit']=='1' && $formularid_edit!="")
                    {
                        $html.='cursor:pointer; ';
                    }
                    $html.='" ';

                    //if(($row & 1)==1)
                    if($rowCounter % 2)
                        $html.='class="ui-state-default roweven" ';
                    else
                        $html.='class="ui-state-default rowodd" ';

                    $rowCounter++;

                    $html.='>';
                    for($col=2;$col<($rs->field_count);$col++)
                    {
                        $stype=$rs->fetch_field_direct($col)->type;
                        $html.='<td 
                        ';
                        if($this->property['showbuttonedit']=='1' && $formularid_edit!="")
                        {
                            $html.='onclick="edit2'.$this->uniqueGridId.'(\''.$row[0].'\',\''.$row[1].'\'); " ';
                        }
                        $html.='
                        style="vertical-align:top; '.($stype=='int'||$stype=='real'?'text-align:right; ':'').($stype=='tinyint'?'text-align:center; ':'').' '.$color.'"
                        >';
                        $html.=$row[$col];
                        $html.='</td>';
                    }
                    
                    
                    
                    if($this->property['showbuttondelete']=='1')
                    {   
                        
                        /*if($_SESSION['test'] == NULL){

                        $_SESSION['getmID'] = mysql_result($rs,$row,0);

                        }*/
                        
                        $html.='<td style="vertical-align:top; width:20px; text-align:center; ">';

                        $tmp=$this->property['textdeletebutton'];
                        if($tmp=="")
                        {
                            $html.='
                            <span style="float:left; cursor:pointer; " class="ui-state-default" title="Delete row"
                            onclick="
                            delete'.$this->uniqueGridId.'(\''.$row[0].'\');
                            ">
                            <span class="ui-icon ui-icon-circle-minus"></span>
                            </span>
                            <div style="clear:both; "></div>
                            ';
                        }
                        else
                        {
                            $html.='
                            <span style="float:left; cursor:pointer; padding:0px 5px; "
                            class="ui-state-default"
                            onclick="
                            delete'.$this->uniqueGridId.'(\''.$row[0].'\');
                            ">
                            '.$tmp.'
                            </span>
                            <div style="clear:both; "></div>
                            ';
                        }
                        $html.='</td>';
                    }

                    if(trim($this->property['navigation'])!='')
                    {
                        $html.='<td nowrap>';
                        
                        $navi=trim($this->property['navigation']);
                        $naviitems=explode("||",$navi);
                        
                        $html.="<table><tr>";
                        for($x=0;$x<count($naviitems);$x++)
                        {
                            $n=explode("|",$naviitems[$x]);
                            
                            $html.='<td>
                                <span style="float:right; cursor:pointer; padding:0px 3px 0px 3px; margin-right:3px; " class="ui-state-default" title="'.$n[0].'"
                                onclick="
                                editnavigation'.$this->uniqueGridId.'(\''.$row[0].'\',\''.$n[2].'\',\''.$n[1].'\');
                                ">
                                '.$n[0].'
                                </span>
                                <td>';
                        }
                        $html.="</tr></table>";

                        $html.='<div style="clear:both; "></div></td>';
                    } 
                    
                    $html.='</tr>';
                }
            }
            $hsconfig->close($rs);
        }
        
        $html.='</table>';
        $html.='
        <div>
        <div style="clear:both; "></div>
        </div>
        </div>
        ';
        return $html;
    }
    
    public function tocsv($text)
    {
      $text=trim($text);
      $text=str_replace('"','""',$text);
      $text='"'.$text.'"';
      return $text;
    }
    

    
    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        
        $html.=parent::getEditorProperty_Textarea("SQL-statment for the flag-rows (first column must be a index, second column gets displayed)",'kennzeichen1sqlstring');
        $html.=parent::getEditorProperty_Checkbox("Should a new button displayed?",'showkennzeichen1buttonnew','0');
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the new button? (ID Form)",'kennzeichen1formularid_new');
        $html.=parent::getEditorProperty_Textbox("Title new button",'kennzeichen1textnewbutton');

        $html.=parent::getEditorProperty_Checkbox("Should a edit button displayed?",'showkennzeichen1buttonedit','0');
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the edit button? (ID Form)",'kennzeichen1formularid_edit');
        $html.=parent::getEditorProperty_Textbox("Title edit button",'kennzeichen1texteditbutton');

        $html.=parent::getEditorProperty_Checkbox("Should a delete button displayed?",'showkennzeichen1buttondelete','0');
        $html.=parent::getEditorProperty_Textbox("Tablename for the deletefunction",'kennzeichen1deletetable');
        $html.=parent::getEditorProperty_Textbox("Columnname with the index for the deletefunction",'kennzeichen1deletecolindex');
        $html.=parent::getEditorProperty_Textarea("SQL-statment to delete subrows (Variables for the current row is #INDEX1#)",'kennzeichen1deletesubrowssqlstring');
        $html.=parent::getEditorProperty_Textbox("Title delete button",'kennzeichen1textdeletebutton');

        $html.=parent::getEditorProperty_Textarea("Own navigation (Schema: DISPLAYNAME|FORMID|WRITE KENNZEICHEN1 IN THAT VARIALBE:#INDEX1#,#INDEX2#)||...",'kennzeichen1navigation');
        
        $html.=parent::getEditorProperty_Checkbox("Display description row?",'kennzeichen1showdesc','0');
        $html.=parent::getEditorProperty_Textarea("Every flag-row executes following sql statement. The retunvalue must be HTML. Variabes: #INDEX1#",'kennzeichen1descsql');
        
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("SQL-statment (first column must be a index, second column must be the foreign index to the groups table) (Variables: #WHERE# #ORDERBY# #LIMIT#)",'sqlstring');
        $html.=parent::getEditorProperty_Textarea("SQL-statment that returns the count from the table (Variables: #WHERE#)",'sqlstringcount');
        $html.=parent::getEditorProperty_Textbox("Rows, that gets displayed in the grid",'limitoffset');
        $html.=parent::getEditorProperty_Textarea("Where-condition (Variables: #INDEX1# #INDEX2#)",'wherefixed');
        $html.=parent::getEditorProperty_Textbox("Columnname and orderdirection from the permanent sortorder",'orderbyfixed');
        $html.=parent::getEditorProperty_Textbox("Columnname from the standardorder that can changed by the user",'orderby');
        $html.=parent::getEditorProperty_Selectbox("Direction from the standardorder that can changed by the user",'orderbydirection',array('ASC'=>'ASC','DESC'=>'DESC'),'ASC');
        $html.=parent::getEditorProperty_Textbox("Columns names seperate by | which can not sort", 'unsortable', '');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("Search (Schema: SQL-COLUMNNAME|TYPE|DISPLAYNAME-NAME||SQL-SPALTENNAME|TYP|ANZEIGE-NAME z. B. mytable.columnname|textbox|displayname||mytable.columnname2|selectboxdb::select id,value from anothertable|displayname2  Type:textbox, selectboxdb, datebox, dateboxmin, dateboxmax)",'wheresearch');
        $html.=parent::getEditorProperty_Line();
        
        $html.=parent::getEditorProperty_Checkbox("Use Row text color?",'showcolor','0');
        $html.=parent::getEditorProperty_Textarea("Every row executes following sql statement. The retunvalue must be a colorvalue in CSS. Variabes: #INDEX1#",'colorsql');
        
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Should a new button displayed?",'showbuttonnew','1');
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the new button? (ID Form)",'formularid_new');
        $html.=parent::getEditorProperty_Textbox("Title new button",'textnewbutton');

        $html.=parent::getEditorProperty_Checkbox("Should a edit button displayed?",'showbuttonedit','1');
        $html.=parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the edit button? (ID Form)",'formularid_edit');

        $html.=parent::getEditorProperty_Checkbox("Should a delete button displayed?",'showbuttondelete','1');
        $html.=parent::getEditorProperty_Textbox("Tablename for the deletefunction",'deletetable');
        $html.=parent::getEditorProperty_Textbox("Columnname with the index for the deletefunction",'deletecolindex');
        $html.=parent::getEditorProperty_Textbox("Title delete button",'textdeletebutton');

        $html.=parent::getEditorProperty_Textarea("Own navigation (Schema: DISPLAYNAME|FORMID|WRITE INDEX1 IN THAT VARIALBE:#INDEX1#,#INDEX2#)||...",'navigation');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("Columnwidth in px with | separated",'colwidth');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Fix height from the table",'fixheight','0');
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}
?>
