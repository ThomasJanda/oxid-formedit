<?php

class selectbox_db extends basecontrol
{
    var $name = "selectbox_db";

    var $editorname        = "Selectbox DB";
    var $editorcategorie   = "Database Items";
    var $editorshow        = true;
    var $editordescription = 'Select box with database connection on the structure of table joins';

    public function getInterpreterRender()
    {
        $hsconfig = getHsConfig();
        $value = parent::getInterpreterRequestValue();
        $interpreterid = $hsconfig->getInterpreterId();

        $e = "";
        if ($this->property['readonly'] == "1") {
            $e .= '<input data-customerid="' . $this->getCustomerId() . '" type="hidden" name="' . $this->id . '" value="' . $value . '">';
        }

        $e .= '<div data-customeridbox="' . $this->getCustomerId() . '" data-hasparentcontrol="' . $this->getParentControl() . '" class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->getParentControlCss() . '' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; ' . ($this->property['invisible'] == "1" ? ' display:none; ' : '') . '">
            <div id="ajaxcontent' . $this->id . '">
            ' . $this->getSelectbox() . '
            </div>
        </div>
        <script type="text/javascript">
        function ajax' . $interpreterid . $this->name . $this->id . '()
            {
                var param="project=' . $hsconfig->getProjectName() . '&elementclass=' . $interpreterid . $this->name . '";
                param+="&elementid=' . $this->id . '";
                param+="&elementfunction=getInterpreterRenderAjax";
                param+="&' . $hsconfig->getInterpreterParameterGet() . '";

                $("#ajaxcontent' . $this->id . '").css("opacity","0.5");

                $.ajax({
                    type: "POST",
                    url: "' . $hsconfig->getBaseUrl() . '/interpreter_ajax.php",
                    data: param,
                    success: function(data)
                    {
                        $("#ajaxcontent' . $this->id . '").html(data);
                    },
                    complete: function() {
                        $("#ajaxcontent' . $this->id . '").css("opacity","1.0");
                    }
                });
            }
            </script>';

        return $e;
    }

    public function getInterpreterRenderAjax()
    {
        return $this->getSelectbox();
    }

    public function getSelectbox()
    {
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        /** @var mysqli $db */
        $db = $hsconfig->getDbId();

        $value = parent::getInterpreterRequestValue();

        $sqlstring = $this->property["standardsqlstring"];
        $sqlstring = str_replace('#INDEX1#', $hsconfig->getIndex1Value(), $sqlstring);
        $sqlstring = str_replace('#INDEX2#', $hsconfig->getIndex2Value(), $sqlstring);
        $sqlstring = str_replace('#KENNZEICHEN1#', $hsconfig->getKennzeichen1Value(), $sqlstring);
        $sqlstring = str_replace('#CURRENTVALUE#', $value, $sqlstring);//BASE-1662
        $sqlstring = $hsconfig->parseSQLString($sqlstring, $value);
        // new feature, replace /*EXTRA()EXTRA*/ tags
        $sqlstring = $this->replaceExtraSql($sqlstring);

        $e = "";
        if ($this->property['debugmode'] == "1") {
            $e .= '<div><b>Select:</b><br>' . htmlentities($sqlstring) . '</div>';
        }

        $e .= '<select ';
        if ($this->property['readonly'] != "1") {
            $e .= 'data-customerid="' . $this->getCustomerId() . '" ';
        }

        if ($value) {
            $e .= 'data-original="' . $value . '" ';
        }

        $select_name = $this->id;
        if ($this->property['multiselect'] == "1") {
            $e           .= " multiple ";
            $select_name .= "[]";
        }

        $e .= ' name="' . $select_name . '" 
            style="vertical-align:middle; width:' . ($this->width - 20) . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; border:1px solid #dddddd; ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'border-color:red; ' : '') . ' ' . ($this->property['readonly'] == "1" ? 'opacity:0.5;' : "") . ' "
            tabindex="' . $this->property['taborder'] . '"
            ' . ($this->property['readonly'] == "1" ? 'readonly="readonly" disabled="disabled"' : "") . '
        >
        ';
        if (!$sqlstring || $this->property->offsetExists('startempty') && (int)$this->property['startempty']) {
            $e .= "<option value='' selected disabled>Select a parent option first</option>";
        } else {
            if ($rs = $db->query($sqlstring)) {
                while ($row = $rs->fetch_row()) {
                    $disabled = isset($row[2]) ? !!$row[2] : false;
                    $disabled = $disabled ? "disabled" : "";
                    $selected = $value == $row[0] ? "selected" : "";
                    $e .= "<option value='$row[0]' $selected $disabled>$row[1]</option>";
                }
                $rs->close();
            }
        }

        $e .= '</select>';
        $e .= '<span class="ui-icon ui-icon-refresh"
            style="cursor:pointer; float:right; "
            onclick="
            if(!$(this).prev().hasAttribute(\'readonly\'))
            { ajax' . $interpreterid . $this->name . $this->id . '(); }
            "></span>';

        if ($this->property->offsetExists('cascadetrigger') && (int)$this->property['cascadetrigger']) {
            $script = '<script type="text/javascript">
            $(document).ready(function(){
                $(this).on("change","[name=' . $this->id . ']",function(){
                    var selectValue = $(this).val(),
                        query = ' . json_encode($this->property['cascadesql']) . ',
                        target = $("[data-customeridbox=' . $this->property['cascadeid'] . ']").find("select"), 
                        interpreter = ' . json_encode($hsconfig->getInterpreterParameterArray()) . ';
                    query = query.replace("#SELECTVALUE#", selectValue);
                     /* call ajax */
                     var param = {
                        project : "' . $hsconfig->getProjectName() . '",
                        elementclass : "' . $interpreterid . $this->name . '",
                        elementid : "' . $this->id . '",
                        elementfunction : "getCascadeValuesAjax",
                        query : query,
                     };
                     for (var key in interpreter) {
                        param[key] = interpreter[key]
                    }
    
                    $("#ajaxcontent' . $this->id . '").css("opacity","0.5");
    
                    $.ajax({
                        type: "POST",
                        url: "' . $hsconfig->getBaseUrl() . '/interpreter_ajax.php",
                        data: param,
                        success: function(data)
                        {
                            var original =target.data("original"); 
                             /* append all option values */
                             target.find("option").remove();
                             data = $.parseJSON(data);
                             if(data.length){
                                 $.each(data,function(key,value){
                                    target.append("<option value="+value[0]+(value[2] ? " disabled":"")+(value[0] === original ? " selected":"")+">"+value[1]+"</option>");
                                 });
                                 target.change();
                             }else{
                                 target.append("<option value=\'\' selected disabled>No value found</option>")
                             }
                        },
                        complete: function() {
                            $("#ajaxcontent' . $this->id . '").css("opacity","1.0");
                        }
                    });
                });
                $("[name=' . $this->id . ']").change();
            });
            ';
            $script .= '</script>';

            $e .= $script;
        }

        return $e;

    }

    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        $html .= parent::getEditorProperty_Checkbox("Allow the user to select multiple elements?", 'multiselect');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textarea("SQL string which samples the data from a table. <br>
            <ul>
                <li>The first column is the one which is stored, usually a foreign key</li>
                <li>The second is the column that is displayed</li>
                <li>The third column is the one used to show disabled or enabled the option on the list. (0=enabled, 1=disabled)</li>
            </ul>
            <br>(Variables #INDEX1#, #INDEX2#, #KENNZEICHEN1#, #CURRENTVALUE#)", 'standardsqlstring');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Required", 'pflichtfeld');
        $html .= parent::getEditorProperty_Textbox("Errormessage", 'fehlermeldung', 'is required');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Readonly", 'readonly');
        $html .= parent::getEditorProperty_Checkbox("Start Empty", 'startempty');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox('Activates another select on change?', 'cascadetrigger');
        $html .= parent::getEditorProperty_Textbox('Target ID', 'cascadeid', '');
        $html .= parent::getEditorProperty_Textarea('SQL that will execute to get values (Variables #SELECTVALUE#)', 'cascadesql');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Debug-modus", 'debugmode', '0');
        $html .= parent::getEditorPropertyFooter(
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

    public function getCascadeValuesAjax()
    {
        $hsConfig = getHsConfig();
        $interpreterId = $hsConfig->getInterpreterId();
        /** @var mysqli $db */
        $db = $hsConfig->getDbId();
        $sSql = preg_replace("/\r|\n/", ' ', $hsConfig->getRequestParameter('query'));
        // Use STARTPARAM in the query ajax
        $sSql = preg_replace_callback('~#STARTPARAM\.([^#]+)#~', function ($match) {
            $pfad = $match[1];
            $startParam = \hsconfig::getInstance()->getInterpreterValue("startparam");

            return isset($startParam[$pfad]) ? $startParam[$pfad] : "";
        }, $sSql);
        $results = [];
        if ($sSql && $rs = $db->query($sSql)) {
            while ($row = $rs->fetch_row()) {
                $results[] = [
                    isset($row[0]) ? $row[0] : '',
                    isset($row[1]) ? $row[1] : '',
                    isset($row[2]) ? (int)$row[2] : 0,
                ];
            }
            $rs->close();
        }
        echo json_encode($results);
        die();
    }



    function getSQL($table)
    {
        $hsConfig = getHsConfig();
        $dbfield=$this->property['datenbankspalte'];
        $dbfield=str_replace('#EDITLANG#','',$dbfield);

        if(trim($dbfield)=="" || $table=="")
            return "";
        $sqlstring = "alter table `".$table."` add column `".$dbfield."` CHAR(50) DEFAULT '' ";

        $dbfielddescription = trim($this->property['datenbankspaltebeschreibung']);
        if($dbfielddescription!="")
        {
            $sqlstring.=" COMMENT '".$hsConfig->escapeString($dbfielddescription)."'";
        }
        $sqlstring.=";";
        $sqlstring.="\n";
        $sqlstring.="ALTER TABLE `".$table."` ADD INDEX `".$dbfield."` (`".$dbfield."`);";

        return $sqlstring;
    }

}
