<?php

class textbox_db extends basecontrol
{
    var $name = "textbox_db";

    var $editorname        = "Textbox DB";
    var $editorcategorie   = "Database Items";
    var $editorshow        = true;
    var $editordescription = 'Simple textbox that can select a value from the database';

    public function getInterpreterRequestValueForDb()
    {
        $this->property['allownull'] = true;
        return parent::getInterpreterRequestValueForDb();
    }
    /*
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $s = false;
        if (isset($this->property['datenbankspalte'])) {
            $hsConfig = getHsConfig();
            $s['col'] = $this->property['datenbankspalte'];
            if ($this->getInterpreterRequestValue() == '') {
                $s['value'] = 'null';
            } else {
                $s['value'] = "'" . $hsConfig->escapeString($this->getInterpreterRequestValue()) . "'";
            }
        }

        return $s;
    }
    */
    /*
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $s = false;
        if (isset($this->property['datenbankspalte'])) {
            $hsConfig = getHsConfig();
            $s['col'] = $this->property['datenbankspalte'];
            if ($this->getInterpreterRequestValue() == '') {
                $s['value'] = 'null';
            } else {
                $s['value'] = "'" . $hsConfig->escapeString($this->getInterpreterRequestValue()) . "'";
            }
        }

        return $s;
    }
    */

    protected function getTable()
    {
        $hsconfig = getHsConfig();

        $limitoffset                  = 16;
        $interpreter_orderby          = $this->getRequestField('orderby', "");
        $interpreter_orderbyDirection = $this->getRequestField('orderbydirection', "");
        $interpreter_page             = $this->getRequestField('page', 0);
        $wherevalue                   = $this->getRequestField('wherevalue', "");
        $index1value                  = $this->getRequestField('index1value', "");
        $index2value                  = $this->getRequestField('index2value', "");
        $kennzeichen1value            = $this->getRequestField('kennzeichen1value', "");

        $sqlstring      = "select " . $this->property['colindex'] . ", " . $this->property['coldesc'] . " as Title FROM " . $this->property['tablename'] . " #WHERE# #ORDERBY# #LIMIT#";
        $sqlstringcount = "select count(*) FROM " . $this->property['tablename'] . " #WHERE#";

        $limit = " LIMIT " . ($interpreter_page * $limitoffset) . ", " . $limitoffset . " ";


        $orderby = "";
        if ($interpreter_orderby != "") {
            if ($orderby != "") {
                $orderby .= ",";
            }
            $orderby .= $interpreter_orderby . " " . $interpreter_orderbyDirection;
        }
        if ($orderby != "") {
            $orderby = " ORDER BY " . $orderby . " ";
        }

//What's this for?
//        $tmpsqlstring = "select " . $this->property['coldesc'] . " FROM " . $this->property['tablename'] . " limit 0,0";
//        $rs           = $hsconfig->execute($tmpsqlstring);
        $wherecol     = $this->property['coldesc'];
        $where        = "";
        $wherehtml    = "<table><tr><td align='right'></td><td>";

        if ($wherevalue != "" && $wherevalue !== 'undefined') {
            $where = " " . $wherecol . " like '%" . str_replace('*', '%', $hsconfig->escapeString($wherevalue)) . "%' ";
        }
        $wherehtml .= "<input 
                type='textbox' 
                id='" . __CLASS__ . $this->id . "wherevalue' 
                name='" . __CLASS__ . $this->id . "wherevalue' 
                value='" . $wherevalue . "'
                style='border:1px solid #dddddd; width:200px; '
                >
            <script type='text/javascript'>
                $('#" . __CLASS__ . $this->id . "wherevalue').keypress(function(event) {
                  if ( event.which == 13 ) 
                  {
                     ajax" . __CLASS__ . $this->id . "(); 
                  }
                });
            </script>";

        $wherehtml .= "</td><td><button type='button' id='" . __CLASS__ . $this->id . "wherebutton' onclick='ajax" . __CLASS__ . $this->id . "()'><span class='ui-icon ui-icon-search'></span></button></td></tr></table>
        <script type='text/javascript'> 
            $('#" . __CLASS__ . $this->id . "wherebutton').button(); 
        </script>";

        if ($where != "") {
            $where = " WHERE " . $where . " ";
        }
        if ($this->property['colwhere'] != "") {
            if ($where != "") {
                $where .= " and ";
            } else {
                $where .= " where ";
            }
            $where .= $this->property['colwhere'];
        }


        $sqlstring = str_replace('#LIMIT#', $limit, $sqlstring);
        $sqlstring = str_replace('#WHERE#', $where, $sqlstring);
        $sqlstring = str_replace('#ORDERBY#', $orderby, $sqlstring);
        $sqlstring = str_replace('#INDEX1#', $index1value, $sqlstring);
        $sqlstring = str_replace('#INDEX2#', $index2value, $sqlstring);
        $sqlstring = str_replace('#KENNZEICHEN1#', $kennzeichen1value, $sqlstring);
        $sqlstring = $hsconfig->parseSQLString($sqlstring);

        //echo $sqlstring;
        $sqlstringcount = str_replace('#LIMIT#', "", $sqlstringcount);
        $sqlstringcount = str_replace('#WHERE#', $where, $sqlstringcount);
        $sqlstringcount = str_replace('#ORDERBY#', $orderby, $sqlstringcount);
        $sqlstringcount = str_replace('#INDEX1#', $index1value, $sqlstringcount);
        $sqlstringcount = str_replace('#INDEX2#', $index2value, $sqlstringcount);
        $sqlstringcount = str_replace('#KENNZEICHEN1#', $kennzeichen1value, $sqlstringcount);
        $sqlstringcount = $hsconfig->parseSQLString($sqlstringcount);

        $html = "";
        if ($this->property['debugmode'] == "1") {
            $html .= '<div><b>Count:</b><br>' . htmlentities($sqlstringcount) . '</div>';
            $html .= '<div><b>Select:</b><br>' . htmlentities($sqlstring) . '</div>';
        }


        $rowcount = $hsconfig->getScalar($sqlstringcount);
        if ($rowcount == "") {
            $rowcount = 0;
        }
        //echo $rowcount;
        $sitecount = ceil($rowcount / $limitoffset);
        $sitehtml  = "";
        if ($sitecount > 1) {
            $sitehtml = '<select style="border:1px solid #dddddd; width:100px; " onchange="
			$(\'#' . __CLASS__ . $this->id . 'page\').val($(this).val());  
			ajax' . __CLASS__ . $this->id . '(); 
            ">';
            for ($x = 0; $x < $sitecount; $x++) {
                $sitehtml .= '<option value="' . $x . '" ' . ($interpreter_page == $x ? 'selected' : '') . '>' . ($x + 1) . '</option>';
            }
            $sitehtml .= '</select>';
        }

        $html .= '
		<div>
		<div>
		';
        if ($sitehtml != "") {
            $html .= '<div style="float:left;"><table><tr><td>' . $sitehtml . '</td></tr></table></div>';
        }

        if ($wherehtml != "") {
            $html .= '<div style="float:right; ">' . $wherehtml . '</div>';
        }

        $html .= '<div style="clear:both; "></div>';
        $html .= '</div>';

        //echo $sqlstring;
        $rs   = $hsconfig->execute($sqlstring);
        if ( $rs ) {
            $html .= '<table style="width:100%; " cellspacing="0" cellpadding="3">';

            $html .= '<tr class="ui-widget-header"><th>' . $rs->fetch_field_direct( 1 )->name . '
		<span style="float:right; cursor:pointer; " class="ui-state-default ' . ($interpreter_orderby == $rs->fetch_field_direct( 1 )->name && $interpreter_orderbyDirection == 'ASC' ? 'ui-state-hover' : '') . '" 
		onclick="
		$(\'#' . __CLASS__ . $this->id . 'orderby\').val(\'' . $rs->fetch_field_direct( 1 )->name . '\'); 
		$(\'#' . __CLASS__ . $this->id . 'orderbydirection\').val(\'ASC\');  
		ajax' . __CLASS__ . $this->id . '(); 
		">
		<span class="ui-icon ui-icon-triangle-1-s "></span>
		</span>
		<span style="float:right; cursor:pointer; " class="ui-state-default ' . ($interpreter_orderby == $rs->fetch_field_direct( 1 )->name && $interpreter_orderbyDirection == 'DESC' ? 'ui-state-hover' : '') . '" 
		onclick="
		$(\'#' . __CLASS__ . $this->id . 'orderby\').val(\'' . $rs->fetch_field_direct( 1 )->name . '\'); 
		$(\'#' . __CLASS__ . $this->id . 'orderbydirection\').val(\'DESC\'); 
		ajax' . __CLASS__ . $this->id . '(); 
		">
		<span class="ui-icon ui-icon-triangle-1-n "></span>
		</span>
		<div style="clear:both; "></div>
		</th></tr>';

            if ( $rs->num_rows == 0 ) {
                $html .= '<tr class="ui-state-default">';
                $html .= '<td colspan="' . $rs->field_count . '"> No data found</td>';
                $html .= '</tr>';
            } else {
                $stype = $rs->fetch_field_direct( 1 )->type;
                while ( $row = $rs->fetch_array( MYSQLI_NUM ) ) {
                    $html .= '<tr style="cursor:pointer; " class="ui-state-default ui-state-hover">
                    <td onclick="select' . __CLASS__ . $this->id . '(\'' . $row[0] . '\',\'' . str_replace( "'", "\'",
                            htmlentities( $row[1] ) ) . '\'); " style="vertical-align:top; ' . ($stype == 'int' || $stype == 'real' ? 'text-align:right; ' : '') . ($stype == 'tinyint' ? 'text-align:center; ' : '') . '">' . $row[1] . '</td></tr>';
                }
            }

            $html .= '</table>';
        }
        $html .= '
		<div>
		';
        if ($sitehtml != "") {
            $html .= '<div style="float:left;">' . $sitehtml . '</div>';
        }
        $html .= '
		<div style="clear:both; "></div>
		</div>
		</div>
		';

        return $html;
    }

    public function getInterpreterRenderAjax()
    {
        return $this->getTable();
    }

    public function getInterpreterRender()
    {
        $hsconfig = getHsConfig();

        $value = "";
        $text  = "";

        $value = parent::getInterpreterRequestValue();
        if ($value != "") {
            $sqlstring = "select " . $this->property['coldesc'] . " from " . $this->property['tablename'] . " where " . $this->property['colindex'] . "='" . $hsconfig->escapeString($value) . "'";
            $text      = $hsconfig->getScalar($sqlstring);
            /*
            $rs=mysql_query($sqlstring,$hsconfig->getDbId());
            if($rs)
            {
                if(mysql_num_rows($rs)==1)
                {
                    $text=mysql_result($rs,0,0);
                }
            }
            */
        }


        $e = '<div data-customeridbox="' . $this->getCustomerId() . '" data-hasparentcontrol="' . $this->getParentControl() . '" class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; ">
            <input 
                readonly="readonly" 
                id="input' . $this->id . '" 
                type="textbox" 
                name="textbox' . $this->id . '" 
                value="' . $text . '" 
                style="vertical-align:middle; width:' . ($this->width - 26) . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; border:1px solid #dddddd; ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'border-color:red; ' : '') . '"
                tabindex="' . $this->property['taborder'] . '"
            >
            <input data-customerid="' . $this->getCustomerId() . '" id="hidden' . $this->id . '" type="hidden" name="' . $this->id . '" value="' . $value . '">
            <span id="span' . $this->id . '" class="ui-icon ui-icon-closethick" style="width:16px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; cursor:pointer; float:right; ">del.</span>
            <input type="hidden" id="' . __CLASS__ . $this->id . 'orderby"          name="' . __CLASS__ . $this->id . 'orderby"          value="">
    		<input type="hidden" id="' . __CLASS__ . $this->id . 'orderbydirection" name="' . __CLASS__ . $this->id . 'orderbydirection" value="">
    		<input type="hidden" id="' . __CLASS__ . $this->id . 'page"             name="' . __CLASS__ . $this->id . 'page"             value="">
            <script>
            	$(function() {
            		$( "#input' . $this->id . '" ).focus(function() {
                        ajax' . __CLASS__ . $this->id . '();
                        $("#ajaxcontent' . $this->id . '").dialog( "open" );
                    });
                    $( "#span' . $this->id . '" ).click(function() { 
                        $( "#input' . $this->id . '" ).val(""); 
                        $( "#hidden' . $this->id . '" ).val(""); 
                    });
        
                    
                    $( "#ajaxcontent' . $this->id . '" ).dialog({
            			autoOpen: false,
            			height: 550,
            			width: 600,
            			modal: true,
            			buttons: {
            				Cancel: function() {
            					$( this ).dialog( "close" );
                                $(".ui-widget-overlay").css("display","none");
            				}
            			},
            			close: function() {
            				$(".ui-widget-overlay").css("display","none");
            			}
            		});
                    
            	});
                
        		function ajax' . __CLASS__ . $this->id . '()
        		{
        			var param="project=' . $hsconfig->getProjectName() . '&elementclass=' . __CLASS__ . '";
        			param+="&elementid=' . $this->id . '";
        			param+="&elementfunction=getInterpreterRenderAjax";
        			param+="&' . __CLASS__ . $this->id . 'orderby=" + $("#' . __CLASS__ . $this->id . 'orderby").val();
        			param+="&' . __CLASS__ . $this->id . 'orderbydirection=" + $("#' . __CLASS__ . $this->id . 'orderbydirection").val();
        			param+="&' . __CLASS__ . $this->id . 'page=" + $("#' . __CLASS__ . $this->id . 'page").val();
                    param+="&' . __CLASS__ . $this->id . 'wherevalue=" + $("#' . __CLASS__ . $this->id . 'wherevalue").val();
                    
                    param+="&' . __CLASS__ . $this->id . 'index1value=' . $hsconfig->getIndex1Value() . '";
                    param+="&' . __CLASS__ . $this->id . 'index2value=' . $hsconfig->getIndex2Value() . '";
                    param+="&' . __CLASS__ . $this->id . 'kennzeichen1value=' . $hsconfig->getKennzeichen1Value() . '";
                    
        			param+="&' . $hsconfig->getInterpreterParameterGet() . '";
                    
                    
        			$.ajax({
        				type: "POST",
        				url: "interpreter_ajax.php",
        				data: param,
                        cache: false,
                        dataType: "html",
        				success: function(data)
        				{
        					$("#ajaxcontent' . $this->id . '").html(data);
        				}
        				});
        		}
        		function select' . __CLASS__ . $this->id . '(id, text)
        		{
                    $("#input' . $this->id . '").val(text);
                    $("#hidden' . $this->id . '").val(id);
                    $("#ajaxcontent' . $this->id . '").dialog( "close" );
                    $(".ui-widget-overlay").css("display","none");
        		}

        	</script>
            <div title="Dialog" id="ajaxcontent' . $this->id . '"><div style="padding:20px; ">Loading Data...</div></div>
        </div>';

        return $e;
    }

    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        //$html.=parent::getEditorProperty_Textbox("Standardtext",'standardtext');
        $html .= parent::getEditorProperty_Textbox("Table name", 'tablename');
        $html .= parent::getEditorProperty_Textbox("Index column name", 'colindex');
        $html .= parent::getEditorProperty_Textarea("Title column name", 'coldesc');
        $html .= parent::getEditorProperty_Textarea("Where clause (Variablen: #INDEX1#, #INDEX2#, #KENNZEICHEN1#)", 'colwhere');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Required", 'pflichtfeld');
        $html .= parent::getEditorProperty_Textbox("Error message", 'fehlermeldung', 'is required');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Debug-Modus", 'debugmode', '0');
        $html .= parent::getEditorPropertyFooter();

        return $html;
    }

    function getSQL($table)
    {
        $dbfield = $this->property['datenbankspalte'];
        if (trim($dbfield) == "" || $table == "") {
            return "";
        }

        return "alter table `" . $table . "` add column `" . $dbfield . "` VARCHAR(250) DEFAULT NULL; ";
    }
}

?>
