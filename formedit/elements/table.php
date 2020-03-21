<?php

include_once __DIR__ . "/inc/table_search_fields.php"; // contains classes that manage search options for tables. main class is: tableSearchField
include_once __DIR__ . "/inc/table_features.php"; // contains classes that add columns/buttons to the table.

class table extends basecontrol
{
    public $name = "table"; // used now for generating js too.
    public $editorname = "Grid";
    public $editorcategorie = "Navigation";
    public $editorshow = true;
    public $editordescription = 'Shows a Grid.';

    public $throughTable; //This property helps to create a sql statement with a through_join and a through_where elements, commonly used to get information which is related over M:N relations

    public $interpreter_page = 0;
    public $interpreter_orderby = "";
    public $interpreter_orderbyDirection = "";
    public $interpreter_multisort = array();

    public $interpreter_kennzeichen1 = "";
    public $interpreter_index2 = "";

    public $uniqueGridId = "";
    public $shortGridId = "";

    // accumulate debug information to display in a single place.
    public $debug = array();

    protected function setUniqueGridId()
    {
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();

        $this->uniqueGridId = $this->name . $interpreterid . $this->id;
        $this->shortGridId = $this->name . $interpreterid;
    }

    /**
     * the idea is that if the interpreterid session variable is cleared, the complete data from this table is deleted too.
     * @param $key
     * @return mixed
     */
    protected function getSession($key)
    {
        // not random anymore
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();

        if ($key == "allkeys") { // for debugging.
            return isset($_SESSION[$interpreterid][$this->uniqueGridId])
                ? $_SESSION[$interpreterid][$this->uniqueGridId]
                : null;
        }

        return isset($_SESSION[$interpreterid][$this->uniqueGridId][$key])
            ? $_SESSION[$interpreterid][$this->uniqueGridId][$key]
            : null;
    }

    /**
     * the idea is that if the interpreterid session variable is cleared, the complete data from this table is deleted too.
     * @param $key
     * @param $value
     */
    protected function setSession($key, $value)
    {
        // not random anymore
        $hsconfig = getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        $_SESSION[$interpreterid][$this->uniqueGridId][$key] = $value;
    }

    public function interpreterInit()
    {
        parent::interpreterInit();

        $this->setUniqueGridId();

        // try to get navigation values from the session of this current form, this is supposed to be cleared every time
        // the project is opened from scratch
        if ($sessionPage = $this->getSession('page')) {
            $this->interpreter_page = ($sessionPage != '' ? $sessionPage : 0);
        }
        if ($sessionOrderBy = $this->getSession('orderby')) {
            $this->interpreter_orderby = $sessionOrderBy;
        }
        if ($sessionOrderByDirection = $this->getSession('orderbydirection')) {
            $this->interpreter_orderbyDirection = $sessionOrderByDirection;
        }
        // request page overwrites session page.
        if (isset($_REQUEST[$this->uniqueGridId . 'page'])) {
            $page                   = $_REQUEST[$this->uniqueGridId . 'page'];
            $this->interpreter_page = ($page != '' ? $page : 0);
        }
        $usersort = false;
        if (isset($_REQUEST[$this->uniqueGridId . 'orderby'])) {
            $this->interpreter_orderby = $_REQUEST[$this->uniqueGridId . 'orderby'];
            $usersort = true;
        }
        if (isset($_REQUEST[$this->uniqueGridId . 'orderbydirection'])) {
            $this->interpreter_orderbyDirection = $_REQUEST[$this->uniqueGridId . 'orderbydirection'];
            $usersort = true;
        }

        if ($usersort && $this->property['enablemultisort'] == "1") {
            $sessionMultiSort = $this->getSession('multisort') ?:[];

            $tmpMultiSort = array();
            foreach ($sessionMultiSort as $str) {
                $str = trim($str);
                if ($str != "") {
                    if (strtolower($str) == strtolower($this->interpreter_orderby . " ASC") || strtolower($str) == strtolower($this->interpreter_orderby . " DESC")) {
                    } else {
                        $tmpMultiSort[] = $str;
                    }
                }
            }
            if ($this->interpreter_orderby != "")
                $tmpMultiSort[] = $this->interpreter_orderby . " " . $this->interpreter_orderbyDirection;

            if ($this->interpreter_orderby == "deleteAllOrder")
                $tmpMultiSort = array();

            $this->interpreter_multisort = $tmpMultiSort;
            $this->setSession('multisort', $tmpMultiSort);
        }

        $this->setSession('orderby', $this->interpreter_orderby);
        $this->setSession('orderbydirection', $this->interpreter_orderbyDirection);
        $this->setSession('page', $this->interpreter_page);

        $hsConfig = getHsConfig();
        $this->interpreter_kennzeichen1 = $hsConfig->getKennzeichen1Value();
        if (isset($_REQUEST[$this->uniqueGridId . 'kennzeichen1']))
            $this->interpreter_kennzeichen1 = $_REQUEST[$this->uniqueGridId . 'kennzeichen1'];

        $this->interpreter_index2 = $hsConfig->getIndex2Value();
        if (isset($_REQUEST['index2value']))
            $this->interpreter_index2 = $_REQUEST['index2value'];

        $this->throughTable = $hsConfig->getThroughValue();
        if (isset($_REQUEST['through']))
            $this->throughTable = $_REQUEST['through'];

    }

    protected function currency_format($number, $dec_point, $thousands_sep, $mindecimals)
    {
        $was_neg = $number < 0; // Because +0 == -0
        $number = abs($number);

        $tmp = explode('.', $number);

        if (isset($tmp[1])) {
            $tmp[1] = str_pad($tmp[1], 2, '0');
        } else
            $tmp[1] = "00";

        $out = number_format($tmp[0], 0, $dec_point, $thousands_sep);
        if (isset($tmp[1])) $out .= $dec_point . $tmp[1];

        if ($was_neg) $out = "-" . $out;

        return $out;
    }

    protected function _replaceSnippet(&$sqlstring, &$sqlstringcount=null)
    {
        //snippet feature
        $sSnippets = $this->getProperty('sqlstringsnippets');
        if($sSnippets!="")
        {
            $aSnippets = array_map('trim',explode("||", $sSnippets));
            foreach($aSnippets as $sSnippet)
            {
                //split at the first "="
                if(strpos($sSnippet, "=")!==false)
                {
                    $sVariable = trim(substr($sSnippet,0,strpos($sSnippet, "=")));
                    if(substr($sVariable,0,1)=="#" && substr($sVariable,strlen($sVariable)-1)=="#" && strlen($sVariable)>1)
                    {
                        $sSnippetSql = trim(substr($sSnippet,strpos($sSnippet, "=") + 1));
                        $sqlstring = str_replace($sVariable,$sSnippetSql,$sqlstring);
                        if($sqlstringcount!==null)
                            $sqlstringcount = str_replace($sVariable,$sSnippetSql,$sqlstringcount);
                    }
                }
            }
        }
    }
    protected function getTable()
    {

        $exportcsv = $this->property['exportcsv'];

        $hsconfig = getHsConfig();

        /** @var mysqli $db */
        $db = $hsconfig->getDbId();

        $limitoffset = $this->property['limitoffset'];
        if ($limitoffset == "" || !is_numeric($limitoffset)) {
            $limitoffset = 50;
        }

        $wherefixed     = trim($this->property['wherefixed']);
        $havingfixed    = trim($this->property['havingfixed']);
        $sqlstring      = $this->property['sqlstring'];
        $sqlstringcount = $this->property['sqlstringcount'];

        $wheresearch = trim($this->property['wheresearch']);
        $havingsearch = trim($this->property['havingsearch']);

        $ipage = $this->interpreter_page;

        list($kennzeichenhtml, $kennzeichencount) = $this->property['showkennzeichen1'] == "1" ? $this->getKennzeichen() : ["", 0];

        // if the two searches exist, on the first one I skip the bottom of the table, and on the second one I
        // skip the top of the table.

        // the values from these searches are applied to the #WHERE# part of the query
        list($wherehtml, $where, $wheretmp, $x, $hasWhereSearch) = $wheresearch ? $this->renderWhereSearch($wheresearch, !!$havingsearch) : ["", "", [], 0, false];

        // if one the two of them are present, add an " and " string in between.
        $where = implode(' and ', array_filter([$wherefixed, $where]));
        if ($where) {
            $where = "where $where";
        }

        // the values from these searches are applied to the #HAVING# part of the query.
        list($havinghtml, $having, $havingtmp, $x, $hasHavingSearch) = $havingsearch ? $this->renderWhereSearch($havingsearch, false, !!$wheresearch, $x) : ["", "", [], 0, false];

        // if one the two of them are present, add an " and " string in between.
        $having = implode(' and ', array_filter([$havingfixed, $having]));
        if ($having) $having = "having $having";

        // the html table where the searches are made is the same for both #WHERE# and #HAVING#
        $wherehtml = $wherehtml . $havinghtml;
        // same feature for #WHEREVALUE.X# applies to having. The index continues where #WHERE# left off.
        $wheretmp = array_merge($wheretmp, $havingtmp);
        // user has entered search values in grid? sometimes we don't show results unless the user has searched.
        $hasusesearch = $hasWhereSearch || $hasHavingSearch;

        // get $orderby, $orderbyArray
        list($orderby, $orderbyArray) = $this->property['enablemultisort'] == "1"
            ? $this->getMultiSort()
            : [$this->getNormalSort(), []];

        // grid can be configured to only show results after a search has been made.
        $forzeZeroResults = $this->property['onlydisplayafterwhere'] == "1" && !$hasusesearch;

        // get limit
        $limit = $forzeZeroResults
            ? $limit = " limit 0"
            : $limit = " limit " . ($ipage * $limitoffset) . ", $limitoffset ";

        if ($orderby != "") $orderby = " order by $orderby ";

        // replace indexes, where orderby, etc.
        $sqlstring = $this->parseGridString($sqlstring, $limit, $where, $having, $orderby);
        // sometimes we need to conditionally add an extra join or extra columns.
        $sqlstring = $this->replaceExtraSql($sqlstring);

        // same with count statement.
        $sqlstringcount = $this->parseGridString($sqlstringcount, "", $where, $having, $orderby);
        $sqlstring = $this->replaceExtraSql($sqlstring);

        // feature #WHEREVALUE.X#
        $sqlstring = $this->replaceWhereValues($wheretmp, $sqlstring);
        $sqlstringcount = $this->replaceWhereValues($wheretmp, $sqlstringcount);

        //If the SQL string contains "limit 0" and "SQL_CALC_FOUND_ROWS", remove SQL_CALC_FOUND_ROWS from the query and hardcode $rowcount=0
        if (preg_match('/limit 0$/i', $sqlstring) && preg_match('/SQL_CALC_FOUND_ROWS/i', $sqlstring)) {
            $sqlstring        = preg_replace('/SQL_CALC_FOUND_ROWS/i', '', $sqlstring);
            $forzeZeroResults = true;//Don't force to count the
            $sqlstringcount   = "/*'LIMIT 0' found at the end of the statement, no query was run to get the count of elements.*/";
        }

        //If the request contains a param called through, then use it to replace some elements on the query
        if (isset($_REQUEST['through']) && !empty($_REQUEST['through'])) {
            $through        = $_REQUEST['through'];
            $sqlstring      = str_replace('#THROUGH#', $through, $sqlstring);
            $sqlstringcount = str_replace('#THROUGH#', $through, $sqlstringcount);
        }

        //snippet feature
        $this->_replaceSnippet($sqlstring,$sqlstringcount);

        /*
        echo $sqlstring;
        die("");
        */

        // Query before count, so we can use the feature sql_calc_found_rows.
        $rsRows = $db->query($sqlstring);
        $queryError = $db->error; // just in case

        $rowcount = 0;
        if ($sqlstringcount && !$forzeZeroResults) {
            if ($rsCount = $db->query($sqlstringcount)) {
                if ($row = $rsCount->fetch_row()) {
                    $rowcount = $row[0];
                }
                $rsCount->close();
            } else {
                $rowcount = $db->error;
            }
        }

        // page count select options.
        $pageCountHtml = $this->getPageCount($rowcount, $limitoffset);

        // Debug info:
        $this->debug[] = "\nKennzeichen:\n" . $this->property['kennzeichen1sqlstring'];
        $this->debug[] = "\nSelect:\n" . $sqlstring;
        $this->debug[] = "\nCount:\n" . $sqlstringcount;

        $this->debug[] = "\nUnique grid Id: $this->uniqueGridId.\nIndexes:(".(($_REQUEST['index1value']??'')|($_REQUEST['index2value']??"")).")\n";

        // start building the final html

        // table top left corner
        $leftHtml = $kennzeichenhtml != "" || $pageCountHtml != "" || $exportcsv == "1" || trim($this->property['colorlegend']) != ""
            ? $this->getTableTopLeftCorner($kennzeichenhtml, $pageCountHtml, $exportcsv, trim($this->property['colorlegend']))
            : "<!-- no left side html -->";

        // table top right corner
        $rightHtml = $wherehtml != ""
            ? "<div style=float:right >$wherehtml</div>"
            : "<!-- no right side html -->";

        // table new entry button.
        $newButtonHtml = "<!-- no new butotn -->";
        if ($this->property['showbuttonnew'] == '1' && $this->property['formularid_new'] != "") {
            $showKennAndHasCount = $this->property['showkennzeichen1'] == "1" && $kennzeichencount > 0;
            $hideKenn = $this->property['showkennzeichen1'] != "1";
            // only show new button if kenn has been selected, because new records use the value of kenn to insert data in tables.
            if ($showKennAndHasCount || $hideKenn) {
                $newButtonHtml = $this->getNewEntryButton();
            }
        }

        // bulk deletion
        $bulkDeletionButton = trim($this->property['button_delete_bulk']) ? $this->getBulkDeletionButton() : '<!-- no bulk deletion -->';
        $bulkDeletionHeader = trim($this->property['button_delete_bulk']) ? $this->getBulkDeletionHeader() : '<!-- no bulk deletion -->';

        // navigation new
        $naviNew = trim($this->property['navigationnew']);
        $naviHtml = $naviNew
            ? $this->getNavigationNew($naviNew)
            : "<!-- no navigation new -->";

        // sort buttons row
        $sortOrderRow = count($orderbyArray) > 0
            ? $this->getSortOrderRow($orderbyArray, $rsRows->field_count)
            : "<!-- no sort buttons -->";

        // header row tds
        $headerTds = $this->getHeaderRowTds($rsRows);

        // generate all rows of html
        $rowsHtml = $this->getHtmlRows($rsRows, $hasusesearch, $queryError);

        $countMessage = $this->getPageCount($rowcount, $limitoffset, true);

        // stitch everything together
        $html = <<<html
        <div data-hasparentcontrol="{$this->getParentControl()}" style="{$this->getParentControlCss()}">
            <div style=margin-bottom:5px >
                $leftHtml
                $rightHtml
                <div style=clear:both ></div>
                <div>
                    <span style=float:right;cursor:pointer class="ui-state-default" title="Refresh">
                        <span class="ui-icon ui-icon-refresh" data-table-refresh
                            data-table-id="$this->id" data-table-unique-id="$this->uniqueGridId">
                        </span>
                    </span>
                    $bulkDeletionButton
                    {$this->getDebugHtml()}
                    $newButtonHtml
                    $naviHtml
                    <div style="clear:both; "></div>
                </div>
            </div>
        </div>
        <table style=width:100% cellspacing="0" cellpadding="3" class="tablecontrol">
            <tr><th data-fe-debug="$this->uniqueGridId" colspan="$rsRows->field_count" style="text-align:center;cursor:pointer" >$countMessage</th></tr>
            $sortOrderRow
            <tr class="ui-widget-header">
                <!--fe_tf_before_tds-->
                $bulkDeletionHeader
                $headerTds
            </tr>
            $rowsHtml
        </table>
        <div>
            <div style=clear:both ></div>
        </div>
html;
        // send all gathered debug info to the markup
        $html = str_replace("#DEBUG#", $this->getDebugInfo(true), $html);

        return $html;
    }

    protected function getTableTopLeftCorner($kennzeichenhtml, $pageCountHtml, $exportcsv, $colorlegend)
    {
        $kennzeichenhtml = $kennzeichenhtml ? "<tr><td align=right>$kennzeichenhtml</td></tr>" : "";
        $pageCountHtml = $pageCountHtml ? "<tr><td align=right>Page:</td><td>$pageCountHtml</td></tr>" : "";

        $colorlegend="";
        $tmplegend = trim($this->property['colorlegend']);
        if($tmplegend!="")
        {
            if($this->property['colorlegendbr']=="1")
                $tmplegend=nl2br($tmplegend);
            $colorlegend="<tr><td style='padding-bottom:15px; ' colspan='2'><b>Legend</b><br>".$tmplegend."<br></td></tr>";
        }


        $exportCsvHtml = $exportcsv != "1" ? "" : <<<html
        <tr><td></td><td>
            <button type="button" title="Export CSV" id="{$this->uniqueGridId}exportcsvbutton" onclick="exportcsv{$this->uniqueGridId}()">
                <span class="ui-icon ui-icon-suitcase"></span>
            </button>
            <script type="text/javascript">
                $("#{$this->uniqueGridId}exportcsvbutton").button();
            </script>
        </td></tr>
html;


        $leftHtml = <<<html
        <div style="float:left;">
            <table>
                $colorlegend
                $kennzeichenhtml
                $pageCountHtml
                $exportCsvHtml
            </table>
        </div>
html;
        return $leftHtml;
    }

    protected function getKennzeichen()
    {
        $hsconfig = getHsConfig();
        /** @var mysqli $db */
        $db = $hsconfig->getDbId();

        $kennzeichen1sqlstring = $this->property['kennzeichen1sqlstring'];
        $kennzeichen1sqlstring = $hsconfig->parseSQLString($kennzeichen1sqlstring);

        $kennzeichencount = 0;
        $optionsHtml = array();
        if ($rs = $db->query($kennzeichen1sqlstring)) {
            $kennzeichencount = $rs->num_rows;
            for ($i = 0; $row = $rs->fetch_row(); $i++) {

                // pre-select first element if no other is selected
                if ($i == 0 && $this->interpreter_kennzeichen1 == "") {
                    $this->interpreter_kennzeichen1 = $row[0];
                }

                // tell the html element which one is selected.
                $selected = $this->interpreter_kennzeichen1 == $row[0]
                    ? "selected"
                    : "";

                $optionsHtml[] = "<option value='$row[0]' $selected>$row[1]</option>";
            }
            $rs->close();
        }
        $optionsHtml = implode("\n", $optionsHtml);

        $newOptionHtml = "";
        if ($this->property['showkennzeichen1buttonnew'] == "1" && $this->property['kennzeichen1formularid_new'] != "") {
            $id = uniqid();
            $newOptionHtml = <<<html
                <td>
                    <span style="float:left; cursor:pointer; " class="ui-state-default" title="Add new">
                        <span class="ui-icon ui-icon-circle-plus" onclick="
                            $('#index1value').val('$id');
                            $('#formularid').val('{$this->property['kennzeichen1formularid_new']}');
                            $('#kennzeichen1value').val('')
                            $('#navi').val('NEW');
                            $('#formular').submit();
                            "></span>
                    </span>
                </td>
html;
        }

        $editOptionHtml = ""; $deleteOptionHtml = ""; $naviOptionsHtml = "";
        if ($kennzeichencount > 0) {
            if ($this->property['showkennzeichen1buttonedit'] == "1" && $this->property['kennzeichen1formularid_edit'] != "") {
                $editOptionHtml = <<<html
                <td>
                    <span style="float:left; cursor:pointer; " class="ui-state-default" title="Edit">
                        <span class="ui-icon ui-icon-document" onclick="
                            if($('#select{$this->uniqueGridId}kennzeichen1').val() != '') {
                                $('#index1value').val($('#select{$this->uniqueGridId}kennzeichen1').val());
                                $('#formularid').val('{$this->property['kennzeichen1formularid_edit']}');
                                $('#kennzeichen1value').val('')
                                $('#navi').val('EDIT');
                                $('#formular').submit();
                            } "></span>
                    </span>
                </td>
html;
            }
            if ($this->property['showkennzeichen1buttondelete'] == "1") {
                $deleteOptionHtml = <<<html
                <td>
                    <span style="float:left; cursor:pointer; " class="ui-state-default" title="Delete"
                        data-delete-kenn-row="select{$this->uniqueGridId}kennzeichen1" data-delete-id="$this->id"
                        data-kenn-hidden="{$this->uniqueGridId}kennzeichen1">
                        <span class="ui-icon ui-icon-circle-minus"></span>
                    </span>
                </td>
html;
            }
            if (trim($this->property['kennzeichen1navigation']) != '') {
                $navi = trim($this->property['kennzeichen1navigation']);
                $naviitems = explode("||", $navi);

                for ($x = 0; $x < count($naviitems); $x++) {
                    $n = explode("|", $naviitems[$x]);

                    $naviOptionsHtml .= <<<html
                        <td><span style="float:left;cursor:pointer;padding:0 3px;margin-right:5px;" class="ui-state-default" title="$n[0]" onclick="
                            if($('#select{$this->uniqueGridId}kennzeichen1').val() != '') {
                                if('$n[2]' == '#INDEX1#') $('#index1value').val($('#select{$this->uniqueGridId}kennzeichen1').val());
                                else if('$n[2]' == '#INDEX2#') $('#index2value').val($('#select{$this->uniqueGridId}kennzeichen1').val());
                                
                                $('#formularid').val('$n[1]');
                                $('#navi').val('EDIT');
                                $('#kennzeichen1value').val('');
                                
                                if($('#formularid').val()) $('#formular').submit();
                            } ">$n[0]</span>
                        </td>
html;
                }
            }
        }

        $kennzeichenhtml = <<<html
            <tr>
                <td align='right'>{$this->property['kennzeichen1title']}:</td><td>
                    <select style='border:1px solid #dddddd; width:250px; {$this->property["kennzeichen1style"]} '
                        data-table-kenn-select data-table-id="$this->id" data-table-unique-id="$this->uniqueGridId"
                        id='select{$this->uniqueGridId}kennzeichen1'>
                            $optionsHtml
                    </select>
                    <script type='text/javascript'>
                        $('#kennzeichen1value').val($('#select{$this->uniqueGridId}kennzeichen1').val());
                    </script>
                </td>
                $newOptionHtml
                $editOptionHtml
                $deleteOptionHtml
                $naviOptionsHtml
            </tr>
html;
        return [$kennzeichenhtml, $kennzeichencount];
    }

    protected function getNewEntryButton()
    {
        $formularid_new = $this->property['formularid_new'];
        $newIndex = uniqid();
        $newButtonHtml = <<<html
        <span style="float:left; cursor:pointer; " class="ui-state-default" title="Add new">
            <span class="ui-icon ui-icon-circle-plus" onclick="
                $('#index1value').val('$newIndex');
                $('#formularid').val('$formularid_new');
                $('#kennzeichen1value').val('$this->interpreter_kennzeichen1');
                $('#navi').val('NEW');
                $('#formular').submit(); ">
            </span>
        </span>
html;
        return $newButtonHtml;
    }

    protected function getBulkDeletionButton()
    {
        $sHtmlButton = <<<html
        <span style=float:right;cursor:pointer;margin-right:7px; class="ui-state-default" title="Delete Multiple Items">
            <button type="button"
                class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" 
                onclick="
                if($('[name=\'bulk_delete[]\']').length){
                    var canSubmit = false;
                    $.each($('[name=\'bulk_delete[]\']'),function(){
                        if($(this).is(':checked')){
                            canSubmit = true;
                        }
                    });
                    if(canSubmit){
                        $('#index1value').val('');
                        $('#formularid').val('$this->containerid');
                        $('#kennzeichen1value').val('$this->interpreter_kennzeichen1');
                        $('#navi').val('BULK_DELETE');
                        $('#formular').find('[name=\'delete_item[]\']').remove();
                        $.each($('[name=\'bulk_delete[]\']'),function(){
                            if($(this).is(':checked')){
                                console.log('selected '+$(this).val());
                                $('#formular').append('<input type=hidden name=delete_item[] value=' + $(this).val() + '>');
                            }
                        });
                        $('#formular').submit();
                    }
                }">
                <span class="ui-button-text"><span class="ui-icon ui-icon-minusthick" style="display: inline-block;vertical-align: middle;"></span> Delete Selected</span>
            </button>
        </span>
html;

        return $sHtmlButton;
    }

    protected function getBulkDeletionHeader()
    {
        $sHeader = <<<html
        <th nowrap="nowrap"><span style="cursor:pointer;" onclick="
        if($('[name=\'bulk_delete[]\']').length > $('[name=\'bulk_delete[]\']:checked').length){
            $('[name=\'bulk_delete[]\']').prop('checked',true);
        }else{
            $('[name=\'bulk_delete[]\']').prop('checked',false);
        }">Select All</span></th>
html;

        return $sHeader;
    }

    protected function getNavigationNew($naviNew)
    {
        $naviHtml = array();
        // removing empty entries with array_filter + trim
        $nx = array_filter(array_map("trim", explode("||", $naviNew)));
        foreach ($nx as $n) {
            list($title, $value) = array_map("trim", explode("|", "$n|")); // adding extra | to avoid list problems
            if ($title != "" && $value != "") {
                $newIndex = uniqid();
                $naviHtml[] = <<<html
                <span style=margin-left:3px;float:left;cursor:pointer class="ui-state-default" title="$title">
                    <span style="cursor:pointer;padding:1px 3px" onclick="
                        $('#index1value').val('$newIndex'); $('#formularid').val('$value');
                        $('#kennzeichen1value').val('$this->interpreter_kennzeichen1')
                        $('#navi').val('NEW'); $('#formular').submit(); ">
                        $title
                    </span>
                </span>
html;
            }
        }
        $naviHtml = implode("\n", $naviHtml);
        return $naviHtml;
    }

    protected function getSortOrderRow($orderbyArray, $colSpan)
    {
        $nx = implode(", ", $orderbyArray);
        $nx = str_replace(" ASC", ' &#9650;', $nx);
        $nx = str_replace(" DESC", ' &#9660;', $nx);
        $sortOrderRow = <<<html
        <tr>
            <th colspan="$colSpan" style=text-align:left >
                <span style=cursor:pointer;float:left;margin-right:10px; class="ui-state-default" title="Remove order"
                    onclick="deleteorder{$this->uniqueGridId}();">
                    <span class="ui-icon ui-icon-circle-minus"></span>
                </span>
                Order by: $nx
            </th>
        </tr>
html;
        return $sortOrderRow;
    }

    /**
     * Get the settings to add question marks on the table header
     *
     * @return array
     */
    protected function _getFormatColumnsQuestionMarks()
    {
        $formatQuestionmarks = [];
        if ($this->property['formatcolumns_questionmarks'] == '1') {
            $qmColumns = explode('|', $this->property['format_questionmarks']);

            foreach ($qmColumns as $qmColumn) {
                if (strpos($qmColumn, "=>") !== false) {
                    $qmValues = explode('=>', $qmColumn);

                    if (count($qmValues) == 2) {
                        $qmField = trim($qmValues[0]);
                        $qmValue = trim($qmValues[1]);

                        if ($qmField != "" && $qmValue != "") {
                            $formatQuestionmarks[$qmField] = $qmValue;
                        }
                    }
                }
            }
        }

        return $formatQuestionmarks;
    }

    protected function getColumnFormatArray()
    {
        // custom format for each column
        $format = array();
        if ($this->property['formatcolumns'] == "1") {

            $nx = explode("|", $this->property['format']);
            foreach ($nx as $f) {
                //find first =
                $f0="";
                $f1="";
                if(strpos($f,"=")!==false)
                {
                    $f0=trim(substr($f,0,strpos($f,"=")));
                    $f1=trim(substr($f,strpos($f,"=")+1));
                    //echo $f0. " => ".$f1."<br>";
                }
                //list($f0, $f1) = array_map("trim", explode("=", "$f=")); // add extra = to avlid list problems
                if ($f0 != "" && $f1 != "") $format[$f0] = $f1;
            }
        }
        return $format;
    }

    protected function getHeaderRowTds($rsRows)
    {

        $questionMarks = $this->_getFormatColumnsQuestionMarks();

        // custom format for each column
        $format = $this->getColumnFormatArray();

        $headerTds = array();
        $colwidth = explode("|", $this->property['colwidth']);
        $fields = $rsRows ? $rsRows->fetch_fields() : [];
        foreach ($fields as $col => $field) {
            if ($col == 0) continue;

            $colW = $col - 1;
            $width = isset($colwidth[$colW]) && $colwidth[$colW] != ""
                ? "width:$colwidth[$colW]px"
                : "";

            $ascActive = $this->interpreter_orderby == $field->name && $this->interpreter_orderbyDirection == 'ASC' ? 'ui-state-hover' : '';
            $descActive = $this->interpreter_orderby == $field->name && $this->interpreter_orderbyDirection == 'DESC' ? 'ui-state-hover' : '';

            //Creates the question mark for the header if exists on the array
            $headerLabel = "$field->name";
            if (key_exists($field->name, $questionMarks)) {
                $qmValue     = $questionMarks[$field->name];
                $headerLabel = "$field->name <span style='cursor:help; color: #ff0084; padding-left: 5px;' title='$qmValue'>&#63;</span>";
            }

            $cellStyle="";
            if (isset($format[$field->name])) {
                $formatcol = $format[$field->name];
                if($formatcol=="hidden" || $formatcol=="color")
                {
                    $cellStyle='border:0; display:none; ';
                }
            }

            $sortFormat="string";
            if (isset($format[$field->name])) {
                if($format[$field->name]=="number")
                {
                    $sortFormat="integer";
                }
                elseif($format[$field->name]=="currency" || $format[$field->name]=="percent")
                {
                    $sortFormat="float";
                }
            }

            //do not display sort
            $sDisplay='inline';
            if(trim($this->property['unsortable'])!="")
            {
                $aUnsortable = explode("|",trim($this->property['unsortable']));
                $aUnsortable = array_map('trim',$aUnsortable);
                if(in_array($field->name,$aUnsortable))
                    $sDisplay = 'none';
            }

            $sortType = ($this->property['enablemultisort'] == "2"?'-browser':'');
            $sHtml = <<<html
            <th nowrap="nowrap" style="$width;$cellStyle">
                $headerLabel<br>
                <span style="float:right;cursor:pointer; display:$sDisplay; "
                    class="ui-state-default $descActive"
                    data-table-order-by$sortType="$field->name" 
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
                    data-table-order-by$sortType="$field->name" 
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

            $headerTds[] = $sHtml;
        }

        if ($this->property['showbuttondelete'] == '1') $headerTds[] = '<th><!-- delete button --></th>';
        if ($this->property['showbuttonarchive'] == '1') $headerTds[] = '<th><!-- archive button --></th>';
        if ($this->property['showselectbox'] == '1') $headerTds[] = '<th><!-- select box --></th>';
        if (trim($this->property['navigation']) != '') $headerTds[] = '<th><!-- navigation --></th>';

        $headerTds = implode("\n", $headerTds);
        return $headerTds;
    }

    protected function getMultiSort()
    {
        // extract direction and field from an order statement.
        $getDirectionAndStr = function ($str) {
            $direction = "ASC";
            if (substr($str, -4) == ' ASC') {
                $str = trim(substr($str, 0, -4));
            } elseif (substr($str, -5) == ' DESC') {
                $str = trim(substr($str, 0, -5));
                $direction = "DESC";
            }
            return [$str, $direction];
        };

        // add quotes to order by statement to avoid sql parsing problems
        $addSurroundQuotes = function ($str) {

            $str = stripslashes($str);
            // if string has table name or has no spaces, no need for quoting.
            $cannotAddQuotes = strpos($str, ".") !== false && strpos($str, " ") === false;
            if (!$cannotAddQuotes) {
                $s = isset($this->property['orderbysurround']) ? $this->property['orderbysurround'] : "`";
                $str = "$s{$str}$s";
            }

            return $str;
            /*
            if (strpos($str, ".") === false) { // something like table.column
                $s = isset($this->property['orderbysurround']) ? $this->property['orderbysurround'] : "`";
                $str = "$s{$str}$s";
            }
            return $str;
            */
        };

        $orderby = ""; $orderbyArray = array();
        if ($str = trim($this->property['orderbyfixed'])) {
            //fixed sort
            list ($str, $direction) = $getDirectionAndStr($str);

            if ($str != "") { // if we have not cut everything by removing DESC and ASC
                $orderbyArray[] = "$str $direction";
                $str = $addSurroundQuotes($str);
                $orderby .= "$str $direction";
            }

        }
        if (count($this->interpreter_multisort) == 0) {
            if ($str = trim($this->property['orderby'])) {
                //standard sort
                list ($str, $direction) = $getDirectionAndStr($str);

                if ($str != "") {
                    $orderbyArray[] = "$str $direction";
                    $str = $addSurroundQuotes($str);
                    if ($orderby) $orderby .= ",";
                    $orderby .= " $str $direction";
                }
            }
        } else {
            foreach ($this->interpreter_multisort as $str) {
                list ($str, $direction) = $getDirectionAndStr($str);

                if ($str != "") {
                    $orderbyArray[] = "$str $direction";
                    $str = $addSurroundQuotes($str);
                    if ($orderby) $orderby .= ",";
                    $orderby .= " $str $direction";
                }
            }
        }

        return [$orderby, $orderbyArray];
    }

    protected function getNormalSort()
    {
        $orderby = "";

        // setting up properties
        if ($this->interpreter_orderby == "" && $this->property['orderby'] != "") {
            $this->interpreter_orderby = $this->property['orderby'];
            $this->interpreter_orderbyDirection = $this->property['orderbydirection'];
        }

        if ($this->interpreter_orderby != "") {
            $tmp = stripslashes($this->interpreter_orderby);
            // if string has table name or has no spaces, no need for quoting.
            $cannotAddQuotes = strpos($this->interpreter_orderby, ".") !== false && strpos($this->interpreter_orderby, " ") === false;
            if (!$cannotAddQuotes) {
                $s = isset($this->property['orderbysurround']) ? $this->property['orderbysurround'] : "`";
                $tmp = "$s{$tmp}$s";
            }
            $orderby .= "$tmp $this->interpreter_orderbyDirection";
        }

        if ($str = trim($this->property['orderbyfixed'])) {
            $middle = $orderby ? "," : "";
            $orderby = "$str $middle $orderby";
        }

        return $orderby;
    }

    protected function getPageCount($rowcount, $limitoffset, $returnMessage = false)
    {
        $pageCount = ceil($rowcount / $limitoffset);
        $pageCountHtml = "";
        $currPage = 0;
        if ($pageCount > 1) {
            $selectOptions = array();
            for ($x = 0; $x < $pageCount; $x++) {
                $selected = $this->interpreter_page == $x;
                if ($selected) $currPage = $x; // let other parts of this class know the current page.
                $selected = $selected ? 'selected' : '';
                $page = $x + 1;
                $selectOptions[] = "<option value='$x' $selected>$page</option>";
            }
            $selectOptions = implode("\n", $selectOptions);

            $pageCountHtml = <<<html
            <select style='border:1px solid #dddddd;width:150px;' data-table-select-page
                data-table-id="$this->id" data-table-unique-id="$this->uniqueGridId">
                $selectOptions
            </select>
html;
        }

        if ($returnMessage) {
            $startRow = $currPage * $limitoffset;
            $endRow = min($limitoffset, $rowcount - $startRow);
            $endRow += $startRow;
            $startRow++;
            return "Displaying $startRow - $endRow of $rowcount";
        }

        return $pageCountHtml;
    }

    protected function parseGridString($sql, $limit, $where, $having, $orderby)
    {
        $sql = str_replace('#LIMIT#', $limit, $sql);
        $sql = str_replace('#WHERE#', $where, $sql);
        $sql = str_replace('#HAVING#', $having, $sql);
        $sql = str_replace('#ORDERBY#', $orderby, $sql);
        $sql = str_replace('#KENNZEICHEN1#', $this->interpreter_kennzeichen1, $sql);
        $sql = str_replace("#EMPTY#", "", $sql); // #EMPTY# is to search for exactly empty entries.;
        $sql = getHsConfig()->parseSQLString($sql);

        // removing empty lines
        $sql = implode("\n", array_filter(array_map("rtrim", explode("\n", $sql))));

        return $sql;
    }

    protected function getDebugHtml()
    {
        $isCookieSet = isset($_REQUEST["fe-debug"]);
        $display1 = $this->property['debugmode'] == "1" || $isCookieSet ? "block" : "none";
        $display2 = $this->property['debugmode'] == "1" ? "block" : "none";
        return "<div data-fe-debug-dude='$this->uniqueGridId' style=float:right;overflow:visible;position:relative;height:18px;width:50px;border-radius:2px;background-color:lightgray;display:$display1;margin-right:5px>
            <div data-fe-debug-toggle style=line-height:18px;text-align:center;cursor:pointer;user-select:none>Debug</div>
            <div style='position:absolute;right:50px;top:0;max-width:800px;max-height:75vh;overflow:scroll;z-index:10;background-color:white;border:1px solid gray;padding:10px;display:$display2'>
                <pre style=font-size:12px>#DEBUG#</pre>
            </div>
        </div>";
    }

    public function getDebugInfo($plain = false)
    {
        $debugInfo = parent::getDebugInfo();
        $this->debug[] = "SESSION (only for this grid):";
        $this->debug[] = print_r($this->getSession("allkeys"), true);
        $debugHtml = htmlentities(implode("\n", $this->debug));
        return $plain ? $debugHtml : array(
            "debugInfo" => $debugHtml
        ) + $debugInfo;
    }

    private function getLinkFromAnchorTag($text)
    {
        //Search the parts of an anchor tag
        $re = '/(<a.*?>)(.*?)(<\/a>)/mi';

        $found = preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
        if ($found > 0) {
            $anchor = $matches[0][1];
            $label  = $matches[0][2];

            //Get the link from the anchor
            $re = '/(.*?href=[\'"])(.*?)([\'"].*?>)/mi';
            preg_match_all($re, $anchor, $matches, PREG_SET_ORDER, 0);

            $link = $matches[0][2];

            $text = "$label: $link";
        }

        return $text;
    }

    public function tocsv($text)
    {
        $hsconfig = getHsConfig();
        if ($hsconfig->isUtf8()) {
            $text = utf8_decode($text);
        }

        $text = trim($text);

        if($this->property['exportnotags']=="1")
            $text = strip_tags($text);
        else
        {
            $text = strip_tags($text, '<a>');
            $text = $this->getLinkFromAnchorTag($text);
        }


        $text = str_replace('"', '""', $text);
        $text = '"' . $text . '"';

        return $text;
    }

    public function toexcel($text)
    {
        $hsconfig = getHsConfig();
        if (!$hsconfig->isUtf8()) {
            $text = utf8_encode($text);
        }

        $text = trim($text);
        $text = str_replace(['<br>', '<br />', '<br/>'], "\n", $text);
        $text = str_replace("\n\n", "\n", $text);
        if($this->property['exportnotags']=="1")
            $text = strip_tags($text);
        else
        {
            $text = strip_tags($text, '<a>');
            $text = $this->getLinkFromAnchorTag($text);
        }




        return $text;
    }

    public function getExportTable()
    {
        if($this->property['exportexcel']=="1")
        {
            $this->getExportTableExcel();
        }
        else
        {
            $this->getExportTableCsv();
        }
    }
    public function getExportTableExcel()
    {
        //try with excel

        $filename = "Export.xlsx";

        $hsconfig = getHsConfig();
        /** @var mysqli $db */
        $db = $hsconfig->getDbId();

        $wherefixed = trim($this->property['wherefixed']);
        $sqlstring = $this->property['sqlstring'];
        $wheresearch = trim($this->property['wheresearch']);
        $havingsearch = trim($this->property['havingsearch']);

        if ($this->property['showkennzeichen1'] == "1") {
            $kennzeichen1sqlstring = $this->property['kennzeichen1sqlstring'];
            if ($rs = $db->query($kennzeichen1sqlstring)) {
                if ($row = $rs->fetch_row()) {
                    if ($this->interpreter_kennzeichen1 == "") {
                        $this->interpreter_kennzeichen1 = $row[0];
                    }
                }
                $rs->close();
            }
        }

        list($wherehtml, $where, $wheretmp, $x) = $wheresearch
            ? $this->renderWhereSearch($wheresearch)
            : ["", "", [], 0];

        $where = implode(' and ', array_filter([$wherefixed, $where]));
        if ($where) $where = "where $where";

        list($havinghtml, $having, $havingtmp) = $havingsearch
            ? $this->renderWhereSearch($havingsearch, false, !!$wheresearch, $x)
            : ["", "", []];

        $wheretmp = array_merge($wheretmp, $havingtmp);

        if ($having) $having = "having $having";

        $orderby = "";

        // get $orderby, $orderbyArray
        list($orderby, $orderbyArray) = $this->property['enablemultisort'] == "1"
            ? $this->getMultiSort()
            : [$this->getNormalSort(), []];

        if ($orderby != "") $orderby = " order by $orderby ";

        $sqlstring = $this->parseGridString($sqlstring, '', $where, $having, $orderby);
        // sometimes we need to conditionally add an extra join or extra columns.
        $sqlstring = $this->replaceExtraSql($sqlstring);

        $sqlstring = $this->replaceWhereValues($wheretmp, $sqlstring);

        //snippet feature
        $this->_replaceSnippet($sqlstring);

        if ($rs = $db->query($sqlstring)) {

            //2018-02-21 exclude the fields with column format hidden
            $format = $this->getColumnFormatArray();

            // header row of the csv
            $fields = $rs->fetch_fields();

            //2018-02-21 exclude the fields with column format hidden
            $aFields=[];
            foreach($fields as $field)
            {
                if(isset($format[$field->name]) && ($format[$field->name]=="hidden" || $format[$field->name]=="color")) {
                    //hidden shouldn´t display in the csv
                }
                else
                {
                    $aFields[]=$field;
                }
            }

            // don't print the index
            array_shift($aFields);
            // only interested on the name of the fields
            $aFields = array_map(function ($f) { return $f->name; }, $aFields);
            $aTable = [];

            // do the same with the rows
            for ($x = 0; $row = $rs->fetch_row(); $x++) {


                //2018-02-21 test for ajaxrequest
                foreach ($fields as $c => $f) {
                    $row[$c] = $this->getRowAjaxCSV($row,$row[$c],$fields,$f->name, $c, $x);
                }

                //2018-02-21 exclude the fields with column format hidden
                $aRow=[];
                foreach ($fields as $c => $f)
                {
                    if(isset($format[$f->name]) && ($format[$f->name]=="hidden" || $format[$f->name]=="color"))
                    {
                        //hidden shouldn´t display in the csv
                    }
                    elseif(isset($format[$f->name]) && $format[$f->name]=="boolean")
                    {
                        //hidden shouldn´t display in the csv
                        if($row[$c]=="1")
                            $aRow[$c]="Y";
                        elseif($row[$c]=="0")
                            $aRow[$c]="N";
                        else
                            $aRow[$c]="";
                        //$aRow[$c]=($row[$c]=="1"?'Y':'N');
                    }
                    else
                    {
                        $v =$row[$c];
                        $v = $this->toexcel($v);
                        $aRow[$c]=$v;
                    }
                }
                array_shift($aRow); // don't print the index

                //echo count($aFields)." ".count($aRow);
                if(count($aFields)==count($aRow))
                {
                    $aTable[($x+1)]=$aRow;
                    /*
                    for($xx=0;$xx<count($aFields);$xx++)
                    {
                        $aTable[($x+1)][$aFields[$xx]]=$aRow[$xx];
                    }
                    */
                }
            }
            $rs->close();

            if(count($aTable)>0)
            {
                include_once __DIR__."/../../../../inc/cexportexcel.php";
                $oExcel = new \cexportexcel();
                $oExcel->setForceDownload(true);
                $oExcel->setFilename($filename);
                //$oExcel->setPath($cconfig3->getShopBaseDir());
                /** start converting into a csv file **/
                $oExcel->exportFromArray($aTable, $aFields);
                unset($oExcel);
            }
        }
    }

    public function getExportTableCsv()
    {
        $filename = "Export.csv";
        $application = "text/csv";

        $hsconfig = getHsConfig();
        $db = $hsconfig->getDbId();

        header("Content-Type: $application");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Description: csv File");
        header("Pragma: no-cache");
        header("Expires: 0");

        $wherefixed = trim($this->property['wherefixed']);
        $sqlstring = $this->property['sqlstring'];
        $wheresearch = trim($this->property['wheresearch']);
        $havingsearch = trim($this->property['havingsearch']);

        if ($this->property['showkennzeichen1'] == "1") {
            $kennzeichen1sqlstring = $this->property['kennzeichen1sqlstring'];
            if ($rs = $db->query($kennzeichen1sqlstring)) {
                if ($row = $rs->fetch_row()) {
                    if ($this->interpreter_kennzeichen1 == "") {
                        $this->interpreter_kennzeichen1 = $row[0];
                    }
                }
                $rs->close();
            }
        }

        list($wherehtml, $where, $wheretmp, $x) = $wheresearch
            ? $this->renderWhereSearch($wheresearch)
            : ["", "", [], 0];

        $where = implode(' and ', array_filter([$wherefixed, $where]));
        if ($where) $where = "where $where";

        list($havinghtml, $having, $havingtmp) = $havingsearch
            ? $this->renderWhereSearch($havingsearch, false, !!$wheresearch, $x)
            : ["", "", []];

        $wheretmp = array_merge($wheretmp, $havingtmp);

        if ($having) $having = "having $having";

        $orderby = "";

        // get $orderby, $orderbyArray
        list($orderby, $orderbyArray) = $this->property['enablemultisort'] == "1"
            ? $this->getMultiSort()
            : [$this->getNormalSort(), []];

        if ($orderby != "") $orderby = " order by $orderby ";

        $sqlstring = $this->parseGridString($sqlstring, '', $where, $having, $orderby);
        // sometimes we need to conditionally add an extra join or extra columns.
        $sqlstring = $this->replaceExtraSql($sqlstring);

        $sqlstring = $this->replaceWhereValues($wheretmp, $sqlstring);

        //snippet feature
        $this->_replaceSnippet($sqlstring);

        if ($rs = $db->query($sqlstring)) {

            //2018-02-21 exclude the fields with column format hidden
            $format = $this->getColumnFormatArray();

            // header row of the csv
            $fields = $rs->fetch_fields();

            //2018-02-21 exclude the fields with column format hidden
            $aFields=[];
            foreach($fields as $field)
            {
                if(isset($format[$field->name]) && ($format[$field->name]=="hidden" || $format[$field->name]=="color")) {
                    //hidden shouldn´t display in the csv
                }
                else
                {
                    $aFields[]=$field;
                }
            }

            // don't print the index
            array_shift($aFields);
            // only interested on the name of the fields
            $aFields = array_map(function ($f) { return $f->name; }, $aFields);
            // call $this->csv on all lines and glue them with ","
            echo implode(",", array_map([$this, "tocsv"], $aFields)) . "\r\n";
            // do the same with the rows
            for ($x = 0; $row = $rs->fetch_row(); $x++) {


                //2018-02-21 test for ajaxrequest
                foreach ($fields as $c => $f) {
                    $row[$c] = $this->getRowAjaxCSV($row,$row[$c],$fields,$f->name, $c, $x);
                }

                //2018-02-21 exclude the fields with column format hidden
                $aRow=[];
                foreach ($fields as $c => $f)
                {
                    if(isset($format[$f->name]) && ($format[$f->name]=="hidden" || $format[$f->name]=="color"))
                    {
                        //hidden shouldn´t display in the csv
                    }
                    elseif(isset($format[$f->name]) && $format[$f->name]=="boolean")
                    {
                        //hidden shouldn´t display in the csv
                        if($row[$c]=="1")
                            $aRow[$c]="Y";
                        elseif($row[$c]=="0")
                            $aRow[$c]="N";
                        else
                            $aRow[$c]="";
                        //$aRow[$c]=($row[$c]=="1"?'Y':'N');
                    }
                    else
                    {
                        $aRow[$c]=$row[$c];
                    }
                }

                array_shift($aRow); // don't print the index
                echo implode(",", array_map([$this, "tocsv"], $aRow)) . "\r\n";
            }
            $rs->close();
        }
    }


    /**
     * Replace #WHEREVALUE.X# tags in queries.
     * @param array $wheretmp  results from renderWhereSearch
     * @param string $sqlstring  subject string (query) to replace.
     * @param bool $debug  print to output
     * @return string replaced string.
     */
    public function replaceWhereValues($wheretmp, $sqlstring) {
        if (strpos($sqlstring, "#WHEREVALUE") === false) return $sqlstring;

        foreach ($wheretmp as $key => $value) {
            $X = $key + 1;
            $this->debug[] = "#WHEREVALUE.$X# = $value";
            $sqlstring = str_replace("#WHEREVALUE.$X#", $value, $sqlstring);
        }
        return preg_replace('~#WHEREVALUE\.\d\d?#~', '', $sqlstring);
    }

    /**
     * @param mysqli_result $rsRows
     * @param bool $hasusesearch
     * @param string $queryError
     * @return string
     */
    protected function getHtmlRows($rsRows, $hasusesearch, $queryError)
    {
        if (!$rsRows) {
            $paddingTds = array();
            if ($this->property['showbuttondelete'] == '1') $paddingTds[] = '<td></td>';
            if ($this->property['showbuttonarchive'] == '1') $paddingTds[] = '<td></td>';
            if ($this->property['showselectbox'] == '1') $paddingTds[] = '<td></td>';
            if (trim($this->property['navigation']) != '') $paddingTds[] = '<td></td>';
            $paddingTds = implode("", $paddingTds);

            $rowsHtml = <<<html
            <tr class="ui-state-default">
                <td style=color:black>
                    db error: $queryError
                </td>
                $paddingTds
            </tr>
html;
        } elseif ($rsRows->num_rows == 0) {
            $colSpan = $rsRows->field_count - 1;

            $explanation = $this->property['onlydisplayafterwhere'] == "1" && !$hasusesearch
                ? "Use search first"
                : "No data found";

            $paddingTds = array();
            if ($this->property['showbuttondelete'] == '1') $paddingTds[] = '<td></td>';
            if ($this->property['showbuttonarchive'] == '1') $paddingTds[] = '<td></td>';
            if ($this->property['showselectbox'] == '1') $paddingTds[] = '<td></td>';
            if (trim($this->property['navigation']) != '') $paddingTds[] = '<td></td>';
            $paddingTds = implode("", $paddingTds);

            $rowsHtml = <<<html
            <tr class="ui-state-default">
                <td style=color:black colspan="$colSpan">
                    $explanation
                </td>
                $paddingTds
            </tr>
html;
        } else {
            $rowsHtml = array();
            $formularid_edit = $this->property['formularid_edit'];
            for ($i = 0; $row = $rsRows->fetch_row(); $i++) {
                $rowsHtml[] = $this->getHtmlRow($row, $rsRows->fetch_fields(), $formularid_edit, $i);
            }
            $rowsHtml = implode("\n", $rowsHtml);
        }

        return $rowsHtml;
    }


    /**
     * @param array $row
     * @param $fields
     * @param $formularid_edit
     * @param $i
     *
     * @return string
     */
    protected function getHtmlRow($row, $fields, $formularid_edit, $i)
    {
        $hsconfig = getHsConfig();

        // custom format for each column
        $format = $this->getColumnFormatArray();

        $color = $this->getRowColor($row, $fields, $format);

        $ajaxEnabled = $this->property['ajax_request_enabled'] == '1' && $this->property['ajax_request_phpscript'] != "";

        $showButtonEdit = $this->property['showbuttonedit'] == '1' && $formularid_edit != "";

        //region Controller Callback
        $aSubReportDefinitions = $this->_getControllerCallbacks();
        //endregion

        $tds = array();
        foreach ($fields as $col => $field) {
            if ($col == 0) continue;

            $val = $row[$col]; // current cell in row/column
            $type = $field->type;

            $onClick="";
            if(substr($val,0,3)!="<a ") {
                $onClick = $showButtonEdit ? "data-edit-row='$row[0]' data-edit-form='{$this->property['formularid_edit']}'" : "";
            }
            $textAlign = in_array($type, ['int', 'real'])
                ? 'right' : ($type == 'tinyint' ? 'center' : 'left');
            $cellStyle="";
            // for edit mode
            $id = "id='tablecell{$this->uniqueGridId}_{$i}_$col'";

            if (isset($format[$field->name])) {
                $formatcol = $format[$field->name];

                $tmp = "sql::";
                if(substr(strtolower($formatcol),0,strlen($tmp))==$tmp)
                {
                    $sql=substr($formatcol,strlen($tmp));
                    $sql = str_replace('#INDEX1#', $row[0], $sql);
                    $sql=$hsconfig->parseSQLString($sql);
                    $formatcol=$hsconfig->getScalar($sql);
                }

                //TODO move this to method formatFooo, like formatNumber
                switch ($formatcol) {
                    case 'link':
                        $val = "<a href='$val'>Link</a>";
                        break;
                }
                if ($formatcol == "number" || $formatcol == "currency" || $formatcol=="percent")
                    $textAlign = 'right';
                if ($formatcol == "boolean" || $formatcol=="date" || $formatcol=="datetime")
                    $textAlign = 'center';

                if ($formatcol == "text")
                    $val = nl2br($val);
                if ($formatcol == "htmltext")
                    $val = nl2br(htmlentities($val, ENT_IGNORE));
                if ($formatcol == "currency")
                {
                    if(substr($val,0,strlen('#AJAXREQUEST'))!="#AJAXREQUEST")
                        $val = "€ ".$this->currency_format($val, ".", ",", 2);
                }
                if ($formatcol == "percent")
                {
                    if(substr($val,0,strlen('#AJAXREQUEST'))!="#AJAXREQUEST")
                        $val = $this->currency_format(round($val,2), ".", ",", 2)."%";
                }
                if ($formatcol == "boolean") {
                    $val = trim($val);

                    switch($val){
                        case "1":
                        case "true":
                            $val = "&#9745;";
                            break;
                        case "0":
                        case "false":
                            $val = "&#9744;";
                            break;
                        default:
                            $val = "";
                    }

                    $cellStyle = "padding:0; font-size:15px; ";
                }
                if($formatcol=="hidden" || $formatcol=="color")
                {
                    $cellStyle='border:0; display:none; ';
                }
            }

            //region Controller Callback - Link placement
            $sControllerCallbackTag = "";
            //Validate if on SubReport definition exist both, name or column
            if (array_key_exists($field->name, $aSubReportDefinitions) || array_key_exists($col, $aSubReportDefinitions)) {
                $oSubReportDefinition = $aSubReportDefinitions[$field->name] ? : $aSubReportDefinitions[$col];

                /*
                $sLink       = sprintf("%s/index.php?cl=%s&fnc=%s&id=%s", $hsconfig->getBaseUrl(), $oSubReportDefinition->cl, $oSubReportDefinition->fnc, $row[0]);
                */
                $sLink = $hsconfig->getBaseUrl().'/index.php?cl='.$oSubReportDefinition->cl.'&fnc='.$oSubReportDefinition->fnc.'&id='.$row[0];
                foreach ($fields as $tmpCol => $tmpField)
                {
                    $sLink.="&data[".rawurlencode($tmpField->name)."]=".rawurlencode($row[$tmpCol]);
                }

                $sControllerCallbackTag = "<a download href='$sLink' onclick='event.stopPropagation();'><i class='ui-button ui-icon {$oSubReportDefinition->icon}'></i></a>";

                unset($sLink);
                unset($oSubReportDefinition);
            }

            if ($sControllerCallbackTag) {
                //Set the icon on the left or right of the cell value, depends on where the text is aligned.
                if ($textAlign != 'right') {
                    $val= "$val $sControllerCallbackTag";
                } else {
                    $val= "$sControllerCallbackTag $val";
                }

                unset($ui_icon);
                unset($sControllerCallbackTag);
            }
            //endregion

            if ($ajaxEnabled) {
                $ajaxValue = $this->getRowAjax($row, $val, $fields, $field->name, $col,$i);
                if ($ajaxValue) $val = $ajaxValue;
            }

            $editModeHtml = "";
            if ($this->property['editmode'] == "1") {
                $editdefinition = $this->getEditModeArray();
                if (isset($editdefinition[$col])) {

                    $editValues="";
                    foreach ($fields as $editCol => $editField) {
                        if($editValues!="") $editValues.="-|-";
                        $editValues.=$editCol."=".$row[$editCol]; // current cell in row/column
                    }
                    $editValues = base64_encode($editValues);

                    $editModeHtml = <<<html
                    <div title="click to edit cell" class="edittablecell"
                        data-table-edit-mode="edit" data-table-id="$this->id" data-table-unique-id="$this->uniqueGridId"
                        data-table-row-id="$row[0]" data-table-col-index="$col" data-table-row-index="$i"
                        data-table-cell-values="$editValues"
                        ></div>
html;
                }
            }

            $val = $this->property['editmode'] == "1"
                ? "<div data-table-edit-mode='content'>$val</div>"
                : $val;


            $tds [] = <<<html
            <td $id $onClick style='vertical-align:top;text-align:$textAlign; $cellStyle'>
                $val
                $editModeHtml
            </td>
html;
        }

        $tds = implode("\n", $tds);

        $evenOdd = $i % 2
            ? "roweven" : "rowodd";
        $cursor = $showButtonEdit
            ? 'cursor:pointer' : '';

        // show delete button at the end of the row "-"
        $show = false;
        $globalDeleteButton = $this->property['showbuttondelete'] == '1';
        if($globalDeleteButton) {
            if ($this->property['showbuttondelete_condition'] == "1") {
                $sql = $this->property['showbuttondelete_condition_sql'];
                $sql = str_replace('#INDEX1#', $row[0], $sql);
                $sql = $hsconfig->parseSQLString($sql);
                $sql = $this->replaceRowValues($row, $sql);
                $showDeleteButton = trim($this->replaceExtraSql($sql));
                if (strpos(strtolower($showDeleteButton), "select") !== false) {
                    $showDeleteButton = $hsconfig->getScalar($sql);
                }
                if ($showDeleteButton == "1") {
                    $show = true;
                }
            } else {
                $show = true;
            }
        }
        $htmlDelete = $show
            ? $this->getRowDeleteButton($row)
            : ($globalDeleteButton ? "<td></td>" : "<!-- no delete button -->");

        // archive. execute custom sql statement with current row as parameter.
        $archiveHtml = $this->property['showbuttonarchive'] == '1'
            ? $this->getRowArchiveButton($row)
            : "<!-- no archive button -->";

        $selectBoxHtml = $this->property['showselectbox'] == '1'
            ? $this->getRowSelectBox($row)
            : "<!-- no select box -->";

        $navigationHtml = trim($this->property['navigation']) != ''
            ? $this->getRowNavigation($row)
            : "<!-- no navigation -->";

        $bulkDeleteCheckbox = $this->property['button_delete_bulk'] ? '<td><input type="checkbox" name="bulk_delete[]" value="' . $row[0] . '"></td>' : '<!-- no bulk delete -->';

        $html = <<<html
    <tr class="ui-state-default $evenOdd" style="color:$color;$cursor">
        <!--fe_tf_before_tds-->
        $bulkDeleteCheckbox        
        $tds
        $htmlDelete
        $archiveHtml
        $selectBoxHtml
        $navigationHtml
    </tr>
html;


        if($ajaxEnabled && $this->property['ajax_group_row']=="1")
        {
            $colSpan=0;
            $fieldName="";
            foreach ($fields as $col => $field)
            {
                if($fieldName=="")
                    $fieldName=$field->name;
                $colSpan++;
            }
            if ($this->property['showbuttondelete'] == '1') $colSpan++;
            if ($this->property['showbuttonarchive'] == '1') $colSpan++;
            if ($this->property['showselectbox'] == '1') $colSpan++;
            if (trim($this->property['navigation']) != '') $colSpan++;

            $col="switchAjaxRow_content";
            $ajaxid = "cell{$this->uniqueGridId}_".$i."_".$col;
            $ajaxValue = $this->getRowAjax($row, "#AJAXREQUEST:is_group_row#", $fields, $fieldName , $col,$i);

            $html.= <<<html
            <tr>
                <td colspan="$colSpan" style="padding:0; ">
                    <div class="switchAjaxRow">
                        <div class="ui-widget-header" style="cursor:pointer; ">
                            <div class="switchAjaxRow_open" style="padding:3px; ">[+] abrir</div>
                            <div class="switchAjaxRow_close" style="padding:3px; display:none; ">[-] cerrar</div>
                        </div>
                        <div class="switchAjaxRow_content" style="padding:3px; display:none; border-bottom:2px solid lightgrey; " id="$ajaxid">
                            $ajaxValue
                        </div>
                    </div>
                </td>
            </tr>
html;
        }




        return $html;
    }

    protected function getRowColor($row, $fields=null, $format=null)
    {
        $hsconfig = getHsConfig();

        $color = "";
        $colorSearch = trim($this->property['colorsql']);
        if ($this->property['showcolor'] == "1" && $colorSearch) {
            $colorSearch = str_replace('#INDEX1#', $row[0], $colorSearch);
            $colorSearch = $hsconfig->parseSQLString($colorSearch);
            // user is replacing by values from the current row
            $colorSearch = $this->replaceRowValues($row, $colorSearch);
            $color = $this->replaceExtraSql($colorSearch);
            // user is replacing by sql query result.
            if (strpos(strtolower($color), "select") !== false) {
                $color = trim($hsconfig->getScalar($color));
            }
            $this->debug[] = "$row[0]: $color";
        }

        //color by formatting column
        if($fields!==null && $format!==null && count($format)>0)
        {
            foreach ($fields as $col => $field) {
                if ($col == 0) {
                    continue;
                }
                if (isset($format[$field->name])) {
                    $formatcol = $format[$field->name];
                    if($formatcol=="color")
                    {
                        $color = $row[$col];
                        break;
                    }
                }
            }
        }

        if (!$color || $color == "") $color = "black";

        return $color;
    }

    protected function getRowAjax($row, $val, $fields, $fieldName, $col, $rowIndex)
    {
        $cellValue = "";

        // allows: #AJAXREQUEST#, #AJAXREQUEST:some_word#, #AJAXREQUESTALL#, #AJAXREQUESTALL:some_word#
        if (preg_match('~^#AJAXREQUEST(ALL)?(?::([^#]+))?#~', $val, $match)) {
            $ajaxRequestAll = $match[1] ?? "";
            $current_parameter = urlencode($match[2] ?? "");
            $current_column_name = urlencode($fieldName);
            $current_cell_value = urlencode($val);
            $current_row_index = urlencode($row[0]);

            $current_row_values = array();
            foreach ($fields as $c => $f) {
                if ($c == 0) continue;
                $d = urlencode($f->name); $v = urlencode($row[$c]);
                $current_row_values[] = "current_row_values[$d]=$v";
            }
            $current_row_values = implode("&", $current_row_values);

            $feData = array(
                "colName" => $current_column_name,
                "cellVal" => $current_cell_value,
                "rowIndex" => $current_row_index,
                "param" => $current_parameter,
                "rowValues" => $current_row_values
            );

            $feData = json_encode($feData);

            $requestAllHtml = $ajaxRequestAll
                ? "data-fe-ajax-all='$this->uniqueGridId-$col'"
                : "";

            $spanId = "$this->uniqueGridId-$rowIndex-$col";

            $cellValue = <<<html
                <span id="$spanId" data-fe-ajax='$feData' $requestAllHtml>
                    Loading data...
                </span>
html;
        }

        if (preg_match('~^#PROCESS(?::([^#]+))?#~', $val, $match)) {
            // we are having issues with this feature, so I will try to call the intended function
            // from the request itself, not making an ajax request.
            $currParam = $match[1] ?? "";
            $cellValue = $this->getImmediateAjaxResult($row, $val, $fields, $fieldName, $currParam);
        }

        return $cellValue;
    }

    protected function getImmediateAjaxResult($row, $val, $fields, $fieldName, $currParam)
    {
        // make a copy of the request because we will mess with it.
        $origRequest = $_REQUEST;
        $origGet = $_GET;
        $origPost = $_POST;

        $current_row_values = array();
        foreach ($fields as $c => $f) {
            if ($c == 0) continue;
            $current_row_values[$f->name] = $row[$c];
        }

        // fake request params
        $fakeRequest = array(
            "current_parameter" => $currParam,
            "current_column_name" => $fieldName,
            "current_cell_value" => $val,
            "current_row_index" => $row[0],
            "current_row_values" => $current_row_values,
        );

        $_REQUEST = $fakeRequest + $_REQUEST;
        $_GET = $fakeRequest + $_GET;
        $_POST = $fakeRequest + $_POST;

        // $aParams['elementfunction'] = "getInterpreterRenderCellAjax";

        try {
            $path = $this->getAjaxFile();
        } catch(Exception $e) {
            $cellValue = "--error-file-not-found--";
        }
        try {

            ob_start();
            include $path;
            $cellValue = ob_get_clean();

        } catch (Throwable $t) {
            $cellValue = "--error-processing-file--";
        }

        // restore the request variable to what it was before this function
        $_REQUEST = $origRequest;
        $_GET = $origGet;
        $_POST = $origPost;

        return $cellValue;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getAjaxFile()
    {
        $hsConfig = getHsConfig();
        $file = $this->property['ajax_request_phpscript'];
        $path = $hsConfig->getProjectBaseDir(). "/scriptphp2/$file";
        if (!file_exists($path)) {
            $path = $hsConfig->getBaseDir() . "/scriptphp2/$file";
        }
        if (!file_exists($path)) {
            throw new Exception("couldn't find file $file for table ajax calls");
        }

        return $path;
    }

    protected function getRowAjaxCSV($row, $val, $fields, $fieldName, $col, $rowIndex)
    {
        $hsConfig = getHsConfig();
        $cellValue = $val;

        // allows: #AJAXREQUEST#, #AJAXREQUEST:some_word#, #AJAXREQUESTALL#, #AJAXREQUESTALL:some_word#
        if (preg_match('~^#AJAXREQUEST(ALL)?(?::([^#]+))?#~', $val, $match)) {

            //call the file direct
            $ajaxUrl = getHsConfig()->getBaseUrl() . "/interpreter_ajax.php"; // for jquery ajax request
            $current_parameter = $match[2] ?? "";
            $current_column_name = $fieldName;
            $current_cell_value = $val;
            $current_row_index = $row[0];

            $aParams = [];
            foreach ($fields as $c => $f) {
                if ($c == 0) continue;
                $d = urlencode($f->name);
                $aParams["current_row_values[".$d."]"] = $row[$c];
            }

            $aParams['project']=$hsConfig->getProjectName();
            $aParams['elementid']=$this->id;
            $aParams['elementfunction']="getInterpreterRenderCellAjax";
            $aParams['current_column_name']=$current_column_name;
            $aParams['current_cell_value']=$current_cell_value;
            $aParams['current_row_index']=$current_row_index;
            $aParams['current_parameter']=$current_parameter;

            $oTab = $this->getTab();
            if($oTab)
                $aParams['formularid']=$oTab->getTabId();


            //maybe some other parameter nessesary

            $sUrl = $ajaxUrl;
            $bFirst = true;
            foreach($aParams as $n => $v)
            {
                $sUrl.=($bFirst?"?":"&");
                $bFirst = false;
                $sUrl.=$n."=".urlencode($v);
            }
            $sUrl.="&".$hsConfig->getInterpreterParameterGet();

            try {
                $cellValue = file_get_contents($sUrl);
            } catch (Throwable $t) {
                $cellValue = "ERROR";
            }

        }

        if (preg_match('~^#PROCESS(?::([^#]+))?#~', $val, $match)) {
            // we are having issues with this feature, so I will try to call the intended function
            // from the request itself, not making an ajax request.
            $currParam = $match[1] ?? "";
            $cellValue = $this->getImmediateAjaxResult($row, $val, $fields, $fieldName, $currParam);
        }

        return $cellValue;
    }

    protected function getRowNavigation($row)
    {
        $navi = trim($this->property['navigation']);
        $navigationJsFunction = "editnavigation" . $this->uniqueGridId;

        // explode by ||, remove empty entries with array_filter + trim
        $naviitems = array_filter(array_map("trim", explode("||", $navi)));

        $naviHtml = array();
        foreach ($naviitems as $naviitem) {
            $n = array_map("trim", explode("|", "$naviitem||")); // adding extra | to avoid problems with list.

            /*
            echo '<pre>';
            print_r($n);
            echo '</pre>';
            */

            foreach ($n as &$nx) {
                $prove = "dbcondition::";
                if (strpos($nx, $prove) === 0) {
                    $sql = substr($nx, strlen($prove));
                    $sql = str_replace('#INDEX1#', $row[0], $sql);
                    $nx = getHsConfig()->getScalar($sql);
                }
            }
            if ($n[0] != "" && $n[1] != "" && $n[2] != "") {
                $naviHtml[] = <<<html
                <td>
                    <span style="float:right;cursor:pointer;padding:0 3px;margin-right:3px" class="ui-state-default" title="$n[0]" onclick="
                        $navigationJsFunction('$row[0]', '$n[2]', '$n[1]','$n[3]');
                    ">$n[0]</span>
                <td>
html;
            }
        }
        $naviHtml = implode("\n", $naviHtml);

        $navigationHtml = <<<html
        <td nowrap style="vertical-align: top; text-align: center; ">
            <table><tr>
                $naviHtml
            </tr></table>
        </td>
html;
        return $navigationHtml;
    }

    protected function getRowSelectBox($row)
    {
        $selectBoxString = trim($this->property['showselectboxsqlstring']);

        /** @var mysqli $db */
        $db = getHsConfig()->getDbId();
        $selectBoxJsFunction = "showselectbox" . $this->uniqueGridId;

        $selectBoxString = str_replace('#INDEX1#', $row[0], $selectBoxString);
        // replacing #COL.X# in text
        $selectBoxString = $this->replaceRowValues($row, $selectBoxString);
        // evaluating /*EXTRA(#COL.X#)EXTRA*/
        $selectBoxString = $this->replaceExtraSql($selectBoxString);

        // debug info:
        $this->debug[] = "SELECTBOX SQLSTRING: $row[0]: $selectBoxString";

        $selectOptions = array();
        $hasDefault = false;
        if (strpos($selectBoxString, "select") === 0) {
            // it is a sql statement
            $dbSelResults = array();
            if ($rs = $db->query($selectBoxString)) {
                while ($sel = $rs->fetch_row()) {
                    $dbSelResults[] = $sel;
                }
                $rs->close();
            }
            foreach ($dbSelResults as $sel) {
                // see if any of the results from the db had a third column with value 1, this is the default value to use.
                $default = isset($sel[2]) ? $sel[2] : 0;
                if ($default) $hasDefault = true;
                $selectOptions[] = array('v' => $sel[0], 'd' => $sel[1], 's' => $default ? "selected" : "");
            }
        } else {
            // format 1|Yes||0|No . removing empty entries with array_filter after trim
            $optionsToParse = array_filter(array_map("trim", explode("||", $selectBoxString)));
            foreach ($optionsToParse as $sel) {
                list($v, $text, $selected) = explode("|", "$sel||"); // adding extra | to make sure list doesn't fail
                // see if any of the results from the explode had a third column with value 1, this is the default value to use.
                if ($selected) $hasDefault = true;
                $selectOptions[] = array('v' => $v, 'd' => $text, 's' => $selected ? "selected" : "");
            }
        }

        $optionsHtml = array();
        if ($hasDefault) {
            $optionsHtml[] = "<option></option>";
        }
        foreach($selectOptions as $option) {
            $optionsHtml[] = "<option value='$option[v]' $option[s]>$option[d]</option>";
        }
        $optionsHtml = implode("\n", $optionsHtml);

        $selectBoxHtml = <<<html
        <td style="vertical-align:top; width:100px; text-align:center; ">
            <select style=width:100px onchange="$selectBoxJsFunction('$row[0]', this.value);">
                $optionsHtml
            </select>
        </td>
html;
        return $selectBoxHtml;
    }

    /**
     * Creates the Archive button which will be added onto the table.
     *
     * @param $row
     *
     * @return string
     */
    protected function getRowArchiveButton($row)
    {
        $archiveTitle = trim($this->property['buttonarchivetext']) != "" ? trim($this->property['buttonarchivetext']) : 'Archive';
        $aTitles      = [];
        if (strpos($archiveTitle, "#COL.") !== false) {
            explode('|', $archiveTitle);//Used to save the array of values for the label of the button.

            $re = '/(#COL\.)(\d*)(#\[)([\w*|]*)(\])/';
            preg_match($re, $archiveTitle, $matches, PREG_OFFSET_CAPTURE, 0);

            //Get the elements from the regexp
            $colIndex = $matches[2][0];
            $aTitles  = explode('|', $matches[4][0]);
        }
        else
            $aTitles[]=$archiveTitle;


        $archiveJsFunction = "archive" . $this->uniqueGridId;

        $archiveCondition = $this->property['archivsqlcondition'];

        //If we have defined an archive condition and the count of titles is 1
        $archiveCondition = str_replace('#INDEX1#', $row[0], $archiveCondition);
        // replacing #COL.X# in text
        $archiveCondition = $this->replaceRowValues($row, $archiveCondition);
        // evaluating /*EXTRA(#COL.X#)EXTRA*/
        $archiveCondition = $this->replaceExtraSql($archiveCondition);

        // debug info:
        $this->debug[] = "ARCHIVECONDITION: $row[0]: $archiveCondition";

        if (strpos(strtolower($archiveCondition), "select") !== false) {
            // it is a sql statement
            $archiveCondition = getHsConfig()->getScalar($archiveCondition);
        }

        if (count($aTitles) == 1) {
            $archiveEnabled = $archiveCondition == "1";
        } else {
            $archiveEnabled = $archiveCondition == 1;
            $archiveTitle   = $aTitles[$row[$colIndex]];
        }

        $onClick = !$archiveEnabled ? "" : "onclick='$archiveJsFunction(\"$row[0]\");'";
        $opacity = $archiveEnabled ? "" : "opacity:0.5";

        $archiveHtml = <<<html
        <td style=vertical-align:top;width:20px;text-align:center>
            <span style=float:left;cursor:default;$opacity class='ui-state-default' title='$archiveTitle' $onClick>
                <span style="padding:0px 3px">$archiveTitle</span>
            </span>
            <div style=clear:both ></div>
        </td>
html;

        return $archiveHtml;
    }

    protected function getRowDeleteButton($row)
    {
        $htmlDelete = <<<html
        <td style=vertical-align:top;width:20px;text-align:center >
            <span style=float:left;cursor:pointer class="ui-state-default" title="Delete"
                data-delete-id="$this->id" data-delete-row="$row[0]" >
                <span class="ui-icon ui-icon-circle-minus"></span>
            </span>
            <div style=clear:both ></div>
        </td>
html;
        return $htmlDelete;
    }

    /**
     * Replace #COL.X# in string, X is the index of values in the table, index 0 is the id of the grid, invisible to
     * the users, index 1 and forward are the columns the user sees.
     * @param $row
     * @param $string
     * @return string replaced with values of row
     */
    protected function replaceRowValues($row, $string) {
        if (strpos($string, "#COL") === false) return $string;

        foreach ($row as $col => $value) {
            $string = str_replace("#COL.$col#", $value, $string);
        }
        return preg_replace('~#COL\.\d\d?#~', '', $string);
    }

    public function getInterpreterRenderAjax()
    {
        return $this->getTable() . <<<html
<script>$(document).trigger("fe-table-reload");</script>
html;
    }
    public function getInterpreterRenderAjaxMessage()
    {

    }

    public function getInterpreterRenderCellAjax($die = true)
    {
        $hsconfig = getHsConfig();

        try {
            $path = $this->getAjaxFile();
        } catch(Exception $e) {
            if ($die) {
                die;
            } else {
                return "";
            }
        }

        ob_start();
        include $path;
        $return = ob_get_contents();
        ob_end_clean();

        if ($die) {
            echo $return;
            die("");
        } else {
            return $return;
        }
    }

    protected $editModeDefinition = null;
    protected function getEditModeArray()
    {
        if ($this->editModeDefinition === null) {
            $this->editModeDefinition = array();
            if ($this->property['editmode'] == "1") {
                $tmp = explode("||", $this->property['editmodedefinition']);
                foreach ($tmp as $t) {
                    list($index, $type, $sqlselect, $sqlupdate, $sqldisplay) = array_map("trim", explode("|", "$t||||")); // adding extra | to avoid list problems.
                    $this->editModeDefinition[$index] = array(
                        'index' => $index,
                        'type' => $type,
                        'sqlselect' => $sqlselect,
                        'sqlupdate' => $sqlupdate,
                        'sqldisplay' => $sqldisplay
                    );
                    if($this->editModeDefinition[$index]['sqldisplay']=="")
                        $this->editModeDefinition[$index]['sqldisplay']=$this->editModeDefinition[$index]['sqlselect'];
                }
            }
        }
        return $this->editModeDefinition;
    }

    public function getInterpreterRenderCellEditMode()
    {
        $editdefinition = $this->getEditModeArray();
        $colindex = $_REQUEST['current_column_index'];
        $rowindex = $_REQUEST['current_row_index'];
        $index1 = $_REQUEST['current_index'];
        $cellvalues = $_REQUEST['current_cell_values'];
        $ret = "";


        $cellvalues2 = base64_decode($cellvalues);
        $cellvalues2 = explode("-|-",$cellvalues2);
        $cellvalues3=array();
        foreach($cellvalues2 as $v)
        {
            $tmp=explode("=",$v);
            $k=$tmp[0];
            array_shift($tmp);
            $v=implode("=",$tmp);
            $cellvalues3[$k]=$v;
        }


        if (isset($editdefinition[$colindex])) {
            $hsconfig = getHsConfig();

            $sqlselect = $editdefinition[$colindex]['sqlselect'];
            $sqlselect = str_replace('#INDEX#', $hsconfig->escapeString($index1, $hsconfig->getDbId()), $sqlselect);
            foreach($cellvalues3 as $k=>$v)
            {
                $sqlselect = str_replace('#VALUE:'.$k.'#', $hsconfig->escapeString($v), $sqlselect);
            }
            $sqlselect = $hsconfig->parseSQLString($sqlselect);
            $value = $hsconfig->getScalar($sqlselect);

            if ($editdefinition[$colindex]['type'] == "textbox") {
                $ret .= <<<html
                <textarea class="edittablecell_textbox" data-table-edit-mode
                    data-table-id="$this->id" 
                    data-table-unique-id="$this->uniqueGridId"
                    data-table-row-id="$index1" 
                    data-table-col-index="$colindex" 
                    data-table-row-index="$rowindex"
                    data-table-cell-values="$cellvalues"
                    >$value</textarea>
html;
            } else {
                $sqllist = explode("::", $editdefinition[$colindex]['type']);
                $sqllist = $sqllist[1];
                $sqllist = $hsconfig->parseSQLString($sqllist);
                $rs = $hsconfig->Execute($sqllist);
                $options = array();
                while($row = $rs->fetch_array(MYSQLI_NUM)) {
                    $selected = $row[0] == $value ? 'selected' : '';
                    $options []= "<option value='$row[0]' $selected>$row[1]</option>";
                }
                $options = implode("\n", $options);
                $ret = <<<html
                <select class="edittablecell_selectbox" data-table-edit-mode
                    data-table-id="$this->id" 
                    data-table-unique-id="$this->uniqueGridId"
                    data-table-row-id="$index1" 
                    data-table-col-index="$colindex" 
                    data-table-row-index="$rowindex"
                    data-table-cell-values="$cellvalues"
                    >
                    $options
                </select>
html;
            }
        }
        return $ret;
    }

    public function getInterpreterRenderCellEditModeSave()
    {
        $editdefinition = $this->getEditModeArray();
        $colindex = $_REQUEST['current_column_index'];
        $rowindex = $_REQUEST['current_row_index'];
        $index1 = $_REQUEST['current_index'];
        $value = trim($_REQUEST['current_value']);

        $cellvalues = base64_decode($_REQUEST['current_cell_values']);
        $cellvalues = explode("-|-",$cellvalues);
        $cellvalues2=array();
        foreach($cellvalues as $v)
        {
            $tmp=explode("=",$v);
            $k=$tmp[0];
            array_shift($tmp);
            $v=implode("=",$tmp);
            $cellvalues2[$k]=$v;
        }


        $ret = "";

        if (isset($editdefinition[$colindex])) {
            $hsconfig = getHsConfig();

            $sqlupdate = $editdefinition[$colindex]['sqlupdate'];
            $sqlupdate = str_replace('#INDEX#', $hsconfig->escapeString($index1), $sqlupdate);

            //If the selected value is 'null', then use null value on the sql statement:
            $sqlupdate = strtolower($value) === 'null' ? str_replace("'#VALUE#'", $hsconfig->escapeString($value), $sqlupdate) : str_replace('#VALUE#', $hsconfig->escapeString($value), $sqlupdate);

            foreach($cellvalues2 as $k=>$v)
            {
                $sqlupdate = str_replace('#VALUE:'.$k.'#', $hsconfig->escapeString($v), $sqlupdate);
            }
            $sqlupdate = $hsconfig->parseSQLString($sqlupdate);
            $hsconfig->Execute($sqlupdate);

            $sqlselect = $editdefinition[$colindex]['sqldisplay'];
            $sqlselect = str_replace('#INDEX#', $hsconfig->escapeString($index1), $sqlselect);
            $sqlselect = str_replace('#VALUE#', $hsconfig->escapeString($value), $sqlselect);

            foreach($cellvalues2 as $k=>$v)
            {
                $sqlselect = str_replace('#VALUE:'.$k.'#', $hsconfig->escapeString($v), $sqlselect);
            }
            $sqlselect = $hsconfig->parseSQLString($sqlselect);
            $ret = $hsconfig->getScalar($sqlselect);
        }
        return $ret;
    }

    public function interpreterDelete($table, $colindex, $indexvalue)
    {
        if ($_REQUEST['elementiddelete'] == $this->id) {

            if (isset($_REQUEST[$this->uniqueGridId . 'showselectbox']) && $_REQUEST[$this->uniqueGridId . 'showselectbox'] != "") {
                if ($this->property['showselectbox'] == '1') {
                    $sql = $this->property['showselectboxexecutesqlstring'];
                    $sql = explode("||", $sql);
                    foreach ($sql as $s) {
                        $tmp = explode("|", $s);
                        if (trim($tmp[0]) == trim($_REQUEST[$this->uniqueGridId . 'showselectbox'])) {
                            $hsconfig = getHsConfig();
                            $sql = trim($tmp[1]);
                            $sql = str_replace("#INDEX1#", $hsconfig->escapeString($_REQUEST['index1value']), $sql);

                            $hsconfig->executeNoReturn($sql);
                            //mysql_query($sql, $hsconfig->getDbId());
                            break;
                        }
                    }
                }
            } elseif (isset($_REQUEST[$this->uniqueGridId . 'archive']) && $_REQUEST[$this->uniqueGridId . 'archive'] == "1") {
                //Perform the Update SQL statement for the Archive button
                if ($this->property['showbuttonarchive'] == '1') {
                    $hsconfig  = getHsConfig();
                    $key_value = $hsconfig->escapeString( $_REQUEST['index1value'] );
                    $archivsql = $this->property['archivsql'];
                    if ( !empty( $archivsql ) ) {
                        $archivsql = str_replace( "#INDEX1#", $key_value, $archivsql );
                        $hsconfig->executeNoReturn( $archivsql );
                    }

                    $archiv_php = $this->property['archive_php'];
                    if ( !empty( $archiv_php ) ) {
                        $matched = preg_match( '/(.*)::(.*)/i', $archiv_php, $matches );
                        if ( $matched === 1 && count( $matches ) === 3 ) {
                            $class  = $matches[1];
                            $method = $matches[2];

                            if ( !class_exists( $class ) ) {
                                throw new Exception( "The clas $class does not exist" );
                            }

                            if ( !method_exists( $class, $method ) ) {
                                throw new Exception( "The method $method is not implemented on the class $class" );
                            }

                            call_user_func( [$class, $method], $key_value );
                        }
                    }
                }
            } else {
                $hsconfig = getHsConfig();

                //tommy new feature 2018-09-26
                //delete rows in sub tables
                if(isset($this->property['deletesubrowssqlstring']))
                {
                    $aSqlDelete = explode("||", $this->property['deletesubrowssqlstring']);
                    foreach($aSqlDelete as $sSql)
                    {
                        $sSql = trim($sSql);
                        if($sSql!="")
                        {
                            $key_value = $hsconfig->escapeString( $_REQUEST['index1value'] );
                            $sSql = str_replace( "#INDEX1#", $key_value, $sSql );
                            $sSql = $hsconfig->parseSQLString($sSql);

                            $hsconfig->executeNoReturn($sSql);
                        }
                    }
                }


                $deletecolindex = $this->property['deletecolindex'];
                $deletetable = $this->property['deletetable'];
                $sqlstring = "delete from `" . $deletetable . "` where `" . $deletecolindex . "`='" . $hsconfig->escapeString($_REQUEST['index1value']) . "'";

                $hsconfig->executeNoReturn($sqlstring);
            }
        }
    }

    /**
     * @param $table
     * @param $colindex
     * @param $indexvalue
     */
    public function interpreterBulkDelete($table, $colindex, $indexvalue)
    {
        $deleteItems = $_REQUEST['bulk_delete'];
        if (count($deleteItems)) {
            $sSql = $this->property['button_delete_bulk_sql'];
            $hsconfig = getHsConfig();
            $deleteItems = array_map(function ($item) use ($hsconfig) {
                return '"' . $hsconfig->escapeString($item) . '"';
            }, $deleteItems);

            $sSql = str_replace("#ITEMS#", implode(',', $deleteItems), $sSql);
            $hsconfig->executeNoReturn($sSql);
        }
    }

    public function interpreterDeleteKennzeichen1($table, $colindex, $indexvalue)
    {
        if ($_REQUEST['elementiddelete'] == $this->id) {
            $hsconfig = getHsConfig();

            $deletecolindex = $this->property['kennzeichen1deletecolindex'];
            $deletetable = $this->property['kennzeichen1deletetable'];
            $sqlstring = "delete from `" . $deletetable . "` where `" . $deletecolindex . "`='" . $hsconfig->escapeString($_REQUEST['index1value']) . "'";
            $hsconfig->executeNoReturn($sqlstring);
            //mysql_query($sqlstring, $hsconfig->getDbId());

            $deletesql = trim($this->property['kennzeichen1deletesubrowssqlstring']);
            if ($deletesql != "") {
                $deletesql = str_replace("#INDEX1#", $hsconfig->escapeString($_REQUEST['index1value']), $deletesql);
                $hsconfig->executeNoReturn($deletesql);
                //mysql_query($deletesql, $hsconfig->getDbId());
            }
        }
    }

    public function getInterpreterRender()
    {
        $cssheight = $this->property['fixheight'] == "1"
            ? "height:{$this->height}px;overflow:scroll;"
            : "";

        $csswidth = "width:".$this->width."px";
        if($this->property['fixwidth']=="0")
        {
            $csswidth = "width:calc(100% - ".($this->left * 2)."px)";
        }

        $property = $this->property;
        $css = $this->property['css'] ?: $this->property['style'];
        $btnArchiveTxt = trim($this->property['buttonarchivetext']) != "" ? trim($this->property['buttonarchivetext']) : 'Archive';

        $customerId = $this->getCustomerId();

        $cssBrowserSort="";
        if($this->property['enablemultisort'] == "2")
        {
            $cssBrowserSort=' opacity:0.5; ';
        }

        $e = <<<html
<div data-customeridbox="$customerId" class="$property[classname]" id="$this->id" data-hasparentcontrol="{$this->getParentControl()}"
        style="{$this->getParentControlCss()};position:absolute;left:{$this->left}px;top:{$this->top}px;$csswidth;margin-bottom:10px;$cssheight;$css" >
    <div id="ajaxcontent{$this->id}" style="$cssBrowserSort">
        {$this->getTable()}
    </div>
    <div id="archivedialog{$this->id}" title="$btnArchiveTxt" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure?</p>
    </div>
    <input type="hidden" id="{$this->uniqueGridId}showselectbox"    name="{$this->uniqueGridId}showselectbox"    value="">
    <input type="hidden" id="{$this->uniqueGridId}archive"          name="{$this->uniqueGridId}archive"          value="">
    <input type="hidden" id="{$this->uniqueGridId}orderby"          name="{$this->uniqueGridId}orderby"          value="{$this->interpreter_orderby}">
    <input type="hidden" id="{$this->uniqueGridId}orderbydirection" name="{$this->uniqueGridId}orderbydirection" value="{$this->interpreter_orderbyDirection}">
    <input type="hidden" id="{$this->uniqueGridId}page"             name="{$this->uniqueGridId}page"             value="{$this->interpreter_page}">
    <input type="hidden" id="{$this->uniqueGridId}where"            name="{$this->uniqueGridId}where"            value="">
    <input type="hidden" id="{$this->uniqueGridId}exportcsv"        name="{$this->uniqueGridId}exportcsv"        value="">
    <input type="hidden" id="{$this->uniqueGridId}kennzeichen1"     name="{$this->uniqueGridId}kennzeichen1"     value="{$this->interpreter_kennzeichen1}">
    
    <script>
    // single script for loading ajax calls
    {$this->getAjaxScript()}
    $(document).trigger("fe-table-reload");
    </script>
    
</div>
html;

        $e .= "<script>\n" . $this->_getCheckboxListenerJavascript() . "\n</script>";

        return $e;
    }

    protected function getAjaxScript(): string
    {
        $conf = getHsConfig();
        $project = $conf->getProjectName();

        $ajaxUrl = getHsConfig()->getBaseUrl() . "/interpreter_ajax.php"; // for jquery ajax request

        $js = <<<js
        window.feTableReloadAjaxCount=0;
        window.ajaxRequest=[];
        $(document).on("fe-table-reload", function() {
            var table = $("#$this->id");
            var ajaxUrl = "$ajaxUrl";
            var project = "$project";
            var tableId = "$this->id";
            var form = $("#formular");
            
            var comonParam = ""
                    + "&project=" + project
                    + "&elementid=" + tableId
                    + "&elementfunction=getInterpreterRenderCellAjax"
                    + "&" + form.serialize();
            
            var customIds = "";
            form.find("input[data-customerid]").each(function() {
                if($(this).attr("data-customerid") !== "") {
                    if($(this).is('[type="checkbox"]')) {
                        value = ($(this).is(":checked") ? 1 : 0);
                    } else {
                        value = $(this).val();
                    }
                    customIds += "&" + $(this).attr("data-customerid") + "=" + value;
                }
            });
            
            
            table.find("span[data-fe-ajax]:not([data-fe-ajax-all])").each(function() {
                var span = $(this);
                var feData = span.data("feAjax");
                
                for (var a in feData) {
                    // when I do json_encode it also makes a url encode for some reason.
                    feData[a] = decodeURIComponent(feData[a].replace(/\+/g,' '));
                }
                
                var param = comonParam
                    + "&current_column_name=" + feData.colName
                    + "&current_cell_value=" + feData.cellVal
                    + "&current_row_index=" + feData.rowIndex
                    + "&current_parameter=" + feData.param
                    + "&" + feData.rowValues
                    + customIds;
                    
                window.feTableReloadAjaxCount++;
                var request = $.ajax({
                    type: "POST",
                    url: ajaxUrl,
                    data: param,
                    success: function(data) {
                        span.html(data);
                        window.feTableReloadAjaxCount--;
                    },
                    error: function(err) {
                        console.error(err);
                        window.feTableReloadAjaxCount--;
                    }
                });
                window.ajaxRequest.push(request);
            });

            var doneAjaxAll = {};
            table.find("span[data-fe-ajax][data-fe-ajax-all]").each(function() {
                
                var span = $(this);
                var feData = span.data("feAjax");
                var ajaxAll = span.data("feAjaxAll");
                var spanId = span.attr("id");
                
                for (var a in feData) {
                    // when I do json_encode it also makes a url encode for some reason.
                    feData[a] = decodeURIComponent(feData[a].replace(/\+/g,' '));
                }
                
                if (!doneAjaxAll[ajaxAll]) {
                    doneAjaxAll[ajaxAll] = {};
                }
                
                doneAjaxAll[ajaxAll][spanId] = {
                    current_column_name: feData.colName,
                    current_cell_value: feData.cellVal,
                    current_row_index: feData.rowIndex,
                    current_parameter: feData.param,
                };
            });
            
            for (var a in doneAjaxAll) {
                
                var groupData = encodeURIComponent(JSON.stringify(doneAjaxAll[a]));
                
                var param = comonParam
                    + "&ajaxAllData=" + groupData
                    + customIds;
                
                window.feTableReloadAjaxCount++;
                var request = $.ajax({
                    type: "POST",
                    url: ajaxUrl,
                    data: param,
                    success: function(data) {
                        for (var a in data) {
                            $("#" + a).html(data[a]);
                        }
                        window.feTableReloadAjaxCount--;
                    },
                    error: function(err) {
                        console.error(err);
                        window.feTableReloadAjaxCount--;
                    }
                });
                window.ajaxRequest.push(request);
            }
            
            if(window.feTableReloadAjaxCount>0)
                feTableReloadFinishProve();
            else
                feTableReloadFinish();
            
        }); 
js;


        $js.= " 
        function feTableReloadFinishProve()
        {
            if(window.feTableReloadAjaxCount<=0)
                feTableReloadFinish();
            else
                setTimeout(function() { feTableReloadFinishProve() }, 100);
        }
        function feTableReloadFinish()
        {
            ";
            if($this->property['enablemultisort'] == "2")
            {
                $js.=' 
                if(window.feTableReloadAjaxCount<=0)
                {
                    if($("span.ui-state-hover[data-table-order-by-browser]").length > 0)
                        $("span.ui-state-hover[data-table-order-by-browser]").click(); 
                    
                    $("#ajaxcontent'.$this->id.'").css("opacity","1.0");
                }
                ';
            }
            $js.= " 
            
        }
        ";

        return $js;
    }

    public function renderWhereSearch($wheresearch, $skipBottom = false, $skipTop = false, $startX = 0)
    {
        $hsconfig = getHsConfig();
        $db = $hsconfig->getDbId();

        // new feature: enable /*EXTRA()...EXTRA*/ in where search.
        $wheresearch = $hsconfig->parseSQLString($wheresearch);
        $wheresearch = $this->replaceExtraSql($wheresearch);

        $this->debug[] = "WHERESEARCH:\n$wheresearch\n-----";

        $wherehtml = $skipTop ? "" : "<table id='{$this->uniqueGridId}wheretable'>\n";
        $where = array();
        $wheretmp = array(); // feature used in #WHEREVALUE.X#

        // get groups, also remove empty entries and trim whitespace.
        $wherefields = array_filter(array_map('trim', explode("||", $wheresearch)));

        $hasusesearch = false;

        foreach ($wherefields as $x => $wherefield) {
            $x += $startX;

            // if this entry is empty, ignore it, happens when the string starts/ends with the separator
            if (!$wherefield) continue;

            // tmp change, if we find a string like this:  "|"  or like this:  '|'  change it temporarily to this token:  #PIPE"#  or  #PIPE'#  then explode, then replace back.
            $wherefield = str_replace('"|"', '#PIPE"#', str_replace("'|'", "#PIPE'#", $wherefield));
            $exploded = array_map("trim", explode("|", "$wherefield|||")); // added dummy pipes to avoid php errors
            // now change back to pipes where we saved them (also, allow for single pipe to come from formedit.
            $exploded = array_map(function ($wf) {
                return str_replace('#PIPE#', '|', str_replace('#PIPE"#', '"|"', str_replace("#PIPE'#", "'|'", $wf)));
            }, $exploded);

            // standard order of arguments
            list($sqlname, $element, $displayname, $standardvalue) = $exploded;


            //parse standardvalues
            $borderLeft="#";
            $borderRight="DAYSBACK#";
            if(substr($standardvalue,0,1) == $borderLeft && substr($standardvalue, -strlen($borderRight)) == $borderRight) {
                //#XDAYSBACK#
                $days = $standardvalue;
                $standardvalue = "";
                $days = str_replace($borderRight, "", $days);
                $days = str_replace($borderLeft, "", $days);
                if (is_numeric($days)) {
                    $time = time() - ($days * 60 * 60 * 24);
                    $standardvalue = date('Y-m-d', $time);
                }
            }

            // start html row.
            $wherehtml .= "<tr><td align='right'>$displayname:</td><td>\n";

            // used as id and name for input elements in search fields.
            $safeDisplayName = preg_replace('~\W+~', "_", str_replace("~", "_", strtolower($displayname)));
            $inputName = $this->id . '_w_' . $safeDisplayName; // trying something new
            $cookieName = $this->id . '_c_' . $safeDisplayName; // trying something new

            $value = $this->getSearchValue($inputName, $standardvalue);
            if(is_array($value))
            {
                $value2=[];
                foreach($value as $v)
                {
                    $value2[] = $hsconfig->escapeString(stripslashes($v));
                }
                $escapedValue = $value2;
            }
            else
                $escapedValue = $hsconfig->escapeString(stripslashes($value));

            // see if user has made a search
            if ($value != "") $hasusesearch = true;
            // feature used in #WHEREVALUE.X#
            $wheretmp[$x] = $escapedValue;

            // get element name without optional sql statements.
            $elementName = explode('::', $element)[0]; // some elements allow :: syntax for extra values.
            // match element name wit a class from the type tableSearchField...
            $tableSearchField_className = "tableSearchField_$elementName";
            // ...and get the rendered result from the class
            if (class_exists($tableSearchField_className)) {
                /** @var tableSearchField $tableSearchField */
                $tableSearchField = new $tableSearchField_className($element, $this->id, $this->uniqueGridId);
                $where[]= $tableSearchField->getSearchSqlCommand($escapedValue, $sqlname);
                $wherehtml .= $tableSearchField->getSearchFieldHtml($inputName, $value);
            } else {
                $wherehtml .= "<span style=color:brown>Error in search field</span>";
            }

            // end html row.
            $wherehtml .= <<<html
                </td>
                <td>
                    <input type="checkbox" name="$cookieName" data-fe-cookiefor="$inputName" data-fe-default="$value" title="Remember this" />
                </td>
            </tr>
html;
        }

        if (!$skipBottom) {
            // end html table with a button for manual submit of search.
            $wherehtml .= <<<html
                <tr><td></td><td>
                    <button type='button' class='table-wherebutton'
                        data-table-btn-search
                        data-table-id="$this->id" data-table-unique-id="$this->uniqueGridId">
                        <span class='ui-icon ui-icon-search'></span>
                    </button>
                </td><td>
                    <span class="ui-icon ui-icon-help" title="If you activate one of these checkboxes the browser will remember your choices."></span>
                </td></tr>
            </table>
            <script> 
                $(function(){fe.e.table.initSearch();});
            </script>
html;
        }

        $where = implode(' and ', array_filter(array_map('trim', $where)));

        return [$wherehtml, $where, $wheretmp, count($wherefields), $hasusesearch];
    }

    public function getSearchValue($inputName, $standardvalue)
    {
        // in the current setup, the cookies are sent inside $_REQUEST, this is not always the case, and if this feature
        // stops working, this might be the reason.

        // this is considered for the session key because each time we load the form from scratch we want to forget
        // these values.
        $sessionKey = "$inputName-iid";

        // If search value comes in the request, store it in session.
        if (isset($_REQUEST[$inputName])) {
        //if ($_REQUEST[$this->uniqueGridId . "where"] == '1') // this doesn't work well with cookies.
            $this->setSession($sessionKey, $_REQUEST[$inputName]);
        }

        // If user did not enter value, but we have a standard value, then use it.
        $sessionValue = $this->getSession($sessionKey);
        //echo $sessionKey." => ".$sessionValue."<br>";
        if (!isset($sessionValue) && $standardvalue!="") {
            $this->setSession($sessionKey, $standardvalue);
        }

        // get value from this search field.
        $value = $this->getSession($sessionKey);

        return $value;
    }

    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();

        // region Flag Drop-box

        $html .= parent::getEditorProperty_Line("Flag dropbox");

        $html .= parent::getEditorProperty_Checkbox("Should the flag-dropbox displayed?", 'showkennzeichen1', '0');
        $html .= parent::getEditorProperty_Textbox("Title from the flag-dropbox", 'kennzeichen1title');

        $html .= parent::getEditorProperty_Textarea("SQL-statment for the flag-dropbox (first column must be a index, second column gets displayed)", 'kennzeichen1sqlstring');
        $html .= parent::getEditorProperty_Checkbox("Should a new button displayed?", 'showkennzeichen1buttonnew', '0');
        $html .= parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the new button? (ID Form)", 'kennzeichen1formularid_new');
        $html .= parent::getEditorProperty_Checkbox("Should a edit button displayed?", 'showkennzeichen1buttonedit', '0');
        $html .= parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the edit button? (ID Form)", 'kennzeichen1formularid_edit');
        $html .= parent::getEditorProperty_Checkbox("Should a delete button displayed?", 'showkennzeichen1buttondelete', '0');
        $html .= parent::getEditorProperty_Textbox("Tablename for the deletefunction", 'kennzeichen1deletetable');
        $html .= parent::getEditorProperty_Textbox("Columnname with the index for the deletefunction", 'kennzeichen1deletecolindex');
        $html .= parent::getEditorProperty_Textarea("SQL-statment to delete subrows (Variables for the current row is #INDEX1#)", 'kennzeichen1deletesubrowssqlstring');
        $html .= parent::getEditorProperty_Textarea("Own navigation (Schema: DISPLAYNAME|FORMID|WRITE KENNZEICHEN1 IN THAT VARIALBE:#INDEX1#,#INDEX2#)||...", 'kennzeichen1navigation');
        $html .= parent::getEditorProperty_Textbox("CSS Style for the selectbox", 'kennzeichen1style');

        // endregion

        // region Sql Statement

        $html .= parent::getEditorProperty_Line("Sql statement", true);


        $html .= parent::getEditorProperty_Textarea("SQL-statment (first column must be a index) (Variables: #WHERE# #HAVING# #ORDERBY# #LIMIT#) (Tipp: SQL_CALC_FOUND_ROWS)", 'sqlstring');
        $html .= parent::getEditorProperty_Textarea("SQL-statment that returns the count from the table (Variables: #WHERE#) (Tipp: SELECT FOUND_ROWS())", 'sqlstringcount');
        $html .= parent::getEditorProperty_Textbox("Rows, that gets displayed in the grid", 'limitoffset');
        $html .= parent::getEditorProperty_Textarea("Where-condition (Variables: #INDEX1# #INDEX2# #KENNZEICHEN1#)", 'wherefixed');
        $html .= parent::getEditorProperty_Textarea("Having-condition (Variables: #INDEX1# #INDEX2# #KENNZEICHEN1#)", 'havingfixed');
        $html .= parent::getEditorProperty_Textarea("SQL-statment snippets. Can use the snippet variables in the SQL-statments above 
        (Variable name have to surround with '#', Variable is separated from the snippet with '=', sequence separated by '||'.
         Example:#NAME1#=min(column)||#NAME2#=max(column)). 
         This feature helps reduce complexity from an sql statment if you have 
         to reuse some snippets again and again. (See: dashboard_avg_shipping_delay_new.cpf)", 'sqlstringsnippets');
        $html .= parent::getEditorProperty_Textbox("Columnname and orderdirection from the permanent sortorder", 'orderbyfixed');
        $html .= parent::getEditorProperty_Textbox("Columnname from the standardorder that can changed by the user", 'orderby');
        $html .= parent::getEditorProperty_Selectbox("Direction from the standardorder that can changed by the user", 'orderbydirection', array('ASC' => 'ASC', 'DESC' => 'DESC'), 'ASC');
        $html .= parent::getEditorProperty_Textbox("Surround columns in the order by clause with (',`)", 'orderbysurround', "`");
        $html .= parent::getEditorProperty_Textbox("Columns names seperate by | which can not sort", 'unsortable', '');


        $html .= parent::getEditorProperty_Label("<b>DB sort</b>: Normal sorting with sql 'order by'<br>
        <b>DB multi sort</b>: Normal sorting with sql 'order by' where the user can select many columns where he can sort by<br>
        <b>Browser sort</b>: Only the table which display in the browser will sort. No DB connection required. Only helpfull if the complete result display on the screen. As data type of the column, the system use the format property from the table. 
        It translate the format of the column to the correct type of sorting.<br>
        <ul>
        <li>integer = number</li>
        <li>float = currency, percent</li>
        <li>string = all others</li>
</ul>");
        $html .= parent::getEditorProperty_Selectbox('Sort type', 'enablemultisort',array('0'=>'DB sort', '1'=>'DB multi sort', '2' => 'Browser sort'),'0');
        //$html .= parent::getEditorProperty_Checkbox("Enabled multisort?", 'enablemultisort', '0');

        // endregion

        //region ControllerCallback

        $html .= $this->_addControllerCallbackSection();

        //endregion

        // region Search

        $html .= parent::getEditorProperty_Line("Search", true);

        $html .= parent::getEditorProperty_Label("You can show some search fields above the table.<br>
        <br>
        Schema: SQL-COLUMNNAME|TYPE|DISPLAYNAME|STANDARDVALUE<br>
        SQL-COLUMNNAME: columnname in the mysql database (e. g. mytable.mycolumn)<br>
        STANDARDVALUE: is optional. If there is a value it use at start. (Also token like #XDAYSBACK#, replace X with number of days. Calculate a date X days in the past)<br>
        <br>
        If you want show more than one field, separate it with ||.<br>
        <br>
        Note: use of pipe characters is normally not allowed inside this place, because they have a special meaning
        Except if surrounded with quotes, this way they will be respected, example: '|' and \"|\". Also this token will
        be converted to a pipe in the final sql statement: #PIPE#.<br>
        To search for empty entries you can use the token #EMPTY# in case an empty string is causing problems.");

        $help = tableSearchField::getAllHelp();
        $html .= parent::getEditorProperty_Label(
            "<div style=overflow-x:scroll><div style=width:1000px>$help</div></div>"
        );
        $html .= parent::getEditorProperty_Textarea("Where (replaces inside the #WHERE# tag)", 'wheresearch');
        $html .= parent::getEditorProperty_Textarea("Having (replaces inside the #HAVING# tag)", 'havingsearch');
        $html .= parent::getEditorProperty_Checkbox("Display data only after user had searched", 'onlydisplayafterwhere', '0');

        // endregion

        // region New, edit, delete

        $html .= parent::getEditorProperty_Line("New, edit, delete", true);

        $html .= parent::getEditorProperty_Checkbox("Should a new button displayed?", 'showbuttonnew', '1');
        $html .= parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the new button? (ID Form)", 'formularid_new');
        $html .= parent::getEditorProperty_Textarea("Own navigation (Schema: DISPLAYNAME|FORMID||...)", 'navigationnew');

        $html .= parent::getEditorProperty_Checkbox("Should a edit button displayed?", 'showbuttonedit', '1');
        $html .= parent::getEditorProperty_SelectboxFormulare("Which form sould get loaded, aber click the edit button? (ID Form)", 'formularid_edit');
        $html .= parent::getEditorProperty_Textarea("Own navigation (Schema: DISPLAYNAME|FORMID|WRITE INDEX1 IN THAT VARIALBE:#INDEX1#,#INDEX2#|OTHER PARAMETER LIKE do=start&id=3||...). On every part you can use a sql statement that returns only one value. e.g. dbcondition::select if(group=1,'Edit group1','Edit group2') from user where index1='#INDEX1#'|dbcondition::select if(group=1,'formgroup1','formgroup2') from user where index1='#INDEX1#'|#INDEX1#", 'navigation');
        $html .= parent::getEditorProperty_Checkbox("Should the edit form open in a new window?", 'showbuttonedit_blank', '0');

        //$html .= parent::getEditorProperty_Checkbox("Display deletebutton only with condition?", 'showbuttonedit_condition', '1');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Should a delete button displayed?", 'showbuttondelete', '1');
        $html .= parent::getEditorProperty_Textbox("Tablename for the deletefunction", 'deletetable');
        $html .= parent::getEditorProperty_Textbox("Columnname with the index for the deletefunction", 'deletecolindex');
        $html .= parent::getEditorProperty_Textarea("SQL-statment to delete rows in subtables (Variables for the current row is #INDEX1#, Separate sql statments with ||). Will execute before the real deletefunction", 'deletesubrowssqlstring');

        $html .= parent::getEditorProperty_Checkbox("Should a delete button only display with following condition?", 'showbuttondelete_condition', '0');
        $html .= parent::getEditorProperty_Textarea("SQLString return 1 if the delete button should display, all other values the delete button wont be display. Variabes: #INDEX1#", 'showbuttondelete_condition_sql');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Should a bulk delete button display?", 'button_delete_bulk', '0');
        $html .= parent::getEditorProperty_Textarea("SQLString executed when bulk deletion is done. Variabes: #ITEMS#", 'button_delete_bulk_sql');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Should a archive button displayed?", 'showbuttonarchive', '0');
        $html .= parent::getEditorProperty_Textarea("SQLString condition if the button is enabled. If the sqlstatment returns 1 it is enabled else disabled. Variabes: #INDEX1#", 'archivsqlcondition');
        $html .= parent::getEditorProperty_Textbox("Text of the button.<br>
For the two different values, split by pipe, this will help to define two status of the button.<br><br>
Usages:<br>
* Archiv: Only one value for the button label.<br>
* #COL.X#[0=Enable|1=Disable]: Depends on the value of the column X, it will be the value that the label will take.", 'buttonarchivetext', 'Archiv|Unarchiv');
        $html .= parent::getEditorProperty_Textarea("SQLString that execute if the user click on the archive button. Variabes: #INDEX1#", 'archivsql');
        $html .= parent::getEditorProperty_Textarea( "Load a '\\Full\\Model\\ClassName' with a 'method' name which receives the index1 value of the row, <br>ex. \\Full\\Model\\ClassName@method('#INDEX1VALUE#')",
            'archive_php' );

        // endregion

        // region Ajax
        $html .= parent::getEditorProperty_Line("Ajax", true);

        $html .= parent::getEditorProperty_Checkbox("Ajax request enabled?", 'ajax_request_enabled', '0');
        $html .= parent::getEditorProperty_Label("If a value from a cell return '#AJAXREQUEST#' a php script calls asyncron.
        It sends the 'current_column_name', 'current_cell_value', 'current_row_index' and the complete 'current_row_values'
        as array to the php scrpt as post parameter. The script returns the values that gets display in the cell. You can
        extend the variable '#AJAXREQUEST#' with a parameter, that will send as parameter with the ajaxrequest (e. g. '#AJAXREQUEST:PARAMETER#').
        The parameter send as 'current_parameter'. Now you can also use #AJAXREQUESTALL# (for full-column at once) and #PROCESS# (same request) for better performance.");

        $html .= parent::getEditorProperty_Checkbox("Enable grouprow? Send parameter 'is_group_row' as parameter", 'ajax_group_row', '0');

        $html .= parent::getEditorProperty_Textbox("PHP Script that gets execute (Folder 'scriptphp2')", 'ajax_request_phpscript');
        // endregion

        // region Edit mode

        $html .= parent::getEditorProperty_Line("Edit mode", true);

        /* 2015-11-17 editmode */
        $html .= parent::getEditorProperty_Checkbox("Enable editmode", 'editmode', '0');
        $html .= parent::getEditorProperty_Label("The editmode change the behavior of the grid. The user can click on a
        small icon in the grid itself and can edit the value of the cell.<br>
        <br>
        You have to define, on which position what element should display and how to save this.");
        $html .= parent::getEditorProperty_Textarea("<b>Definition of the editmode:</b><br>
        index of the column, started by 1|<br>
        type of element (textbox, selectboxdb)|<br>
        sql how to load value (variables #INDEX1#, #VALUE:0-x#)|<br>
        sql how to save (variables #INDEX#, #VALUE#, #VALUE:0-x#)|<br>
        sql how to load display value (#INDEX#, #VALUE#, #VALUE:0-x#)<br>
        (<br>
        #INDEX# = Index of the current row<br>
        #VALUE# = Value the use type in or select<br>
        #VALUE:0-x# = Value of the column 0-x. Example for value of column 1 use #VALUE:1#. Counting start at 0)<br>
        )<br>
        <br>
        The definitions can seperate by || if you have more than one column.<br>
        If the third sql statment is not definied, the table element use the first sql statment.<br>
        <br>
        Examples:<br>
        1|textbox|select column1 from mytable where index1='#INDEX1#'|update mytable set column1='#VALUE#' where index1='#INDEX#'||<br>
        2|selectboxdb::select index1, title from othertable|select column2 from mytable where index1='#INDEX1#'|update mytable set column2='#VALUE#' where index1='#INDEX#'|select title from othertable where index1='#VALUE#'
        ", 'editmodedefinition');
        /* 2015-11-17 editmode */

        // endregion

        // region Select box row

        $html .= parent::getEditorProperty_Line("Select box row", true);

        $html .= parent::getEditorProperty_Checkbox("Should a selectbox displayed?", 'showselectbox', '0');
        $html .= parent::getEditorProperty_Textarea("SQL statment that display the values in the selectbox. First column is the value, the second is the text that displayed, third if the option is selected (0 or 1). (Variables: #INDEX1#)", 'showselectboxsqlstring');
        $html .= parent::getEditorProperty_Textarea("SQL statment that execute if in the selectbox a value choosen. (Variables: #INDEX1#)<br>
        Syntax:<br>
        value from the selectbox|sql statment that should execute||<br>
        value from the selectbox|sql statment that should execute||<br>
        ...", 'showselectboxexecutesqlstring');

        // endregion

        // region Format columns

        $html .= parent::getEditorProperty_Line("Format columns", true);

        $html .= parent::getEditorProperty_Label("You can format the columns for better reading<br>
        COLUMNNAME=TYPE|COLUMNNAME=TYPE<br>
        COLUMNNAME is the name that displayed in the table header<br>
        TYPE can be number, currency, text<br>
        TYPE number = right aligned<br>
        TYPE currency = right aligned and formated<br>
        TYPE percent = right aligned and formated<br>
        TYPE text = convert newline to html breaks<br>
        TYPE htmltext = convert special html code to text and convert newline to html breaks<br>
        TYPE date = center aligned<br>
        TYPE datetime = center aligned<br>
        TYPE boolean = center aligned, display a check or a minus (1 = &#9745;, 0 = &#9744;)<br>
        TYPE hidden = column will not display (width:0)<br>
        TYPE link = column will be shown as a link, the value of the cell will be the href value<br>
        TYPE color = use the value of the column as css text color of the row (e. g. red, green, #333333). The column is hidden.<br>
        <!--TYPE compressed = extract data<br>
        TYPE compressedtext = extract data and convert new line into html breaks<br>
        TYPE compressedhtmltext = extract data, convert special html code to text and convert new line into html breaks<br>-->
        TYPE sql::select if(type=mail,'compressedtext','compressedhtmltext') from mytable where cpid='#INDEX1#' = can choose the type by a sql statment<br>
        Example:
        Order Number=number|Total=currency|Description=text
        ");
        $html .= parent::getEditorProperty_Checkbox("Format columns", 'formatcolumns', '0');
        $html .= parent::getEditorProperty_Textarea("Format", 'format');

        $html .= $this->_addQuestionMarkFormatSection();

        // endregion

        // region Own navigation
        //$html .= parent::getEditorProperty_Line("Own navigation", true);
        //$html .= parent::getEditorProperty_Textarea("Own navigation (Schema: DISPLAYNAME|FORMID|WRITE INDEX1 IN THAT VARIALBE:#INDEX1#,#INDEX2#|OTHER PARAMETER LIKE do=start&id=3||...). On every part you can use a sql statement that returns only one value. e.g. dbcondition::select if(group=1,'Edit group1','Edit group2') from user where index1='#INDEX1#'|dbcondition::select if(group=1,'formgroup1','formgroup2') from user where index1='#INDEX1#'|#INDEX1#", 'navigation');
        // endregion

        // region Color and size

        $html .= parent::getEditorProperty_Line("Color and size", true);

        $html .= parent::getEditorProperty_Checkbox("Use Row text color?", 'showcolor', '0');
        $html .= parent::getEditorProperty_Textarea("Every row executes following sql statement. The retunvalue must be a colorvalue in CSS. Variabes: #INDEX1#", 'colorsql');
        $html .= parent::getEditorProperty_Textarea("Legend where you can describe what the colors mean", 'colorlegend');
        $html .= parent::getEditorProperty_Checkbox("Replace new line in legend text with html &lt;br&gt;", 'colorlegendbr', '1');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textbox("Columnwidth in px with | separated", 'colwidth');
        $html .= parent::getEditorProperty_Checkbox("Fix height from the table", 'fixheight', '0');
        $html .= parent::getEditorProperty_Checkbox("Fix width from the table, otherwise 100% - 2 times left", 'fixwidth', '1');

        // endregion

        // region Other Settings

        $html .= parent::getEditorProperty_Line("Other settings", true);

        $html .= parent::getEditorProperty_Checkbox("Export as CSV file", 'exportcsv', '0');
        $html .= parent::getEditorProperty_Checkbox("Instead of CSV use XLSX", 'exportexcel', '0');
        $html .= parent::getEditorProperty_Checkbox("Remove HTML tags during export (otherwise a tag will convert into text:link, a tag and code have to be in the same line)", 'exportnotags', '0');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Debug-modus", 'debugmode', '0');
        $html .= parent::getEditorProperty_Line();

        $html .= parent::getEditorPropertyFooter(true, false, false, true, true, true, true, true);

        // endregion

        return $html;
    }

    /**
     * Creates a section to define the question marks that should be shown on the table headers.
     *
     * @return string
     */
    protected function _addQuestionMarkFormatSection()
    {
        $html = parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Label("<b>Question marks</b><br>
<p>
    You can add Question Marks on the headers of the table. This would help the user to understand where the value came from or what means.
</p>
<p>
    The way to use this feature is simple:<br>
    ColumnName=>Question mark text|ColumnName2=>Question mark text for the other column
</p>");
        $html .= parent::getEditorProperty_Checkbox("Add question marks", 'formatcolumns_questionmarks', '0');
        $html .= parent::getEditorProperty_Textarea("Question marks", 'format_questionmarks');


        return $html;
    }

    /**
     * output the javascript functions that apply to this grid
     * @return string
     */
    public function interpreterFinishJavascript()
    {

        $buttonEdit = $this->property['showbuttonedit_blank'] == 1 ? 'fe.e.table.newWindow = true;' : '';
        $hsConfig = getHsConfig();
        $baseUrl = $hsConfig->getBaseUrl();
        $property = $this->property;

        $js = <<<js
        function deleteorder{$this->uniqueGridId}() {
            $("#{$this->uniqueGridId}orderby").val("deleteAllOrder");
            ajax{$this->uniqueGridId}();
        }
        
        /* open in a new window - start - 2015-07-03 */
        var keydetect_{$this->uniqueGridId}_ctrl=false;
        var keydetect_{$this->uniqueGridId}_shift=false;
        var keydetect_{$this->uniqueGridId}_altgr=false;
        $("body").keydown(function(event) {
            if(event.which == 17)
                keydetect_{$this->uniqueGridId}_ctrl=true;
            else if(event.which == 16)
                keydetect_{$this->uniqueGridId}_shift=true;
            else if(event.which == 18)
                keydetect_{$this->uniqueGridId}_altgr=true;
            else
            {
                keydetect_{$this->uniqueGridId}_ctrl=false;
                keydetect_{$this->uniqueGridId}_shift=false;
                keydetect_{$this->uniqueGridId}_altgr=false;
            }
        });
        $("body").keyup(function(event) {
            if(event.which == 17)
                keydetect_{$this->uniqueGridId}_ctrl=false;
            else if(event.which == 16)
                keydetect_{$this->uniqueGridId}_shift=false;
            else if(event.which == 18)
                keydetect_{$this->uniqueGridId}_altgr=false;
            else {
                keydetect_{$this->uniqueGridId}_ctrl=false;
                keydetect_{$this->uniqueGridId}_shift=false;
                keydetect_{$this->uniqueGridId}_altgr=false;
            }
        });
        $("body").focusout(function() {
            keydetect_{$this->uniqueGridId}_ctrl=false;
            keydetect_{$this->uniqueGridId}_shift=false;
            keydetect_{$this->uniqueGridId}_altgr=false;
        });
        $("body").blur(function() {
            keydetect_{$this->uniqueGridId}_ctrl=false;
            keydetect_{$this->uniqueGridId}_shift=false;
            keydetect_{$this->uniqueGridId}_altgr=false;
        });
        
        $buttonEdit
        
        function edit{$this->uniqueGridId}(id) {
            if(keydetect_{$this->uniqueGridId}_shift==true)
                return;
            if(keydetect_{$this->uniqueGridId}_altgr==true)
                return;
    
            var newwindow=false;
            if(keydetect_{$this->uniqueGridId}_ctrl==true)
                newwindow=true;
    
            $('#index1value').val(id);
            $('#formularid').val('$property[formularid_edit]');
            $('#navi').val('EDIT');
            
            $buttonEdit
            
            if(newwindow)
                $('#formular').attr("target","_blank");
    
            $('#formular').submit();
    
            if(newwindow) {
                $('#formular').attr("target","");
                keydetect_{$this->uniqueGridId}_ctrl=false;
            }
        }
    
        function edit2{$this->uniqueGridId}(id,kid) {
            $('#index1value').val(id);
            $('#kennzeichen1value').val(kid);
            $('#formularid').val('$property[formularid_edit]');
            $('#navi').val('EDIT');
            $('#formular').submit();
        }
        function editnavigation{$this->uniqueGridId}(id,idcol,idform,params) {
            params = params || '';
            
            if(params!="")
            {
                var p=params.split("&");
                p.forEach(function (s, i, o) {
                    var v = s.split("=");
                    
                    var input = document.createElement("input");
                    input.setAttribute("type", "hidden");
                    input.setAttribute("name", v[0]);
                    input.setAttribute("value", v[1]);   
                    
                    //append to form element that you want .
                    document.getElementById("formular").appendChild(input);
                });
    
            }
    
            
            if(idcol=="#INDEX1#")
                $('#index1value').val(id);
            else if(idcol=="#INDEX2#")
                $('#index2value').val(id);
            $('#formularid').val(idform);
            $('#navi').val('EDIT');
            if($('#formularid').val()!="")
                $('#formular').submit();
        }
        
        function exportcsv{$this->uniqueGridId}() {
            var param="project={$hsConfig->getProjectName()}";
            param+="&elementid={$this->id}";
            param+="&elementfunction=getExportTable";
            param+="&{$hsConfig->getInterpreterParameterGet()}";
            param+="&{$this->uniqueGridId}kennzeichen1=" + $("#select{$this->uniqueGridId}kennzeichen1").val();
            window.open("$baseUrl/interpreter_ajax.php?" + param);
        }
    
        function archive{$this->uniqueGridId}(id) {
            $( "#archivedialog{$this->id}" ).dialog({
                resizable: false,
                height:140,
                modal: true,
                buttons: {
                    "OK": function() {
                        $("#index1value").val(id);
                        $("#navi").val("DELETE");
                        $("#{$this->uniqueGridId}archive").val("1");
                        $("#elementiddelete").val("{$this->id}");
                        $("#formular").submit();
                    }, Cancel: function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
        }
    
        function showselectbox{$this->uniqueGridId}(index1, value) {
            $("#index1value").val(index1);
            $("#navi").val("DELETE");
            $("#{$this->uniqueGridId}showselectbox").val(value);
            $("#elementiddelete").val("{$this->id}");
            $("#formular").submit();
        }
js;
        return $js;
    }

    public static function interpreterFinish_static()
    {
        $html = <<<html
    <div data-delete-dialog title="Delete" style="display:none; ">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Delete item. Are you sure?</p>
    </div>
html;
        return $html;
    }

    public static $alreadyInjected = false;

    // output the javascript functions that should be global to all grid elements.
    public static function interpreterFinishJavascript_static()
    {
        //This prevents to inject multiple times the same javascript from its extended classes
        if (static::$alreadyInjected) {
            return "<!-- Javascript already injected -->";
        }
        static::$alreadyInjected = true;

        $hsConfig = getHsConfig();
        $baseUrl = $hsConfig->getBaseUrl();

        $js = <<<js
        
        //----- Table grid js begin -----
        
        // global namespace for the Table element.
        fe.e.table = {};
        fe.e.table.ajaxUrl = "$baseUrl/interpreter_ajax.php";
js;
        $jsDebug = <<<js
        
        // show/hide debug info.
        fe.doc.on("click", '[data-fe-debug]', function(e) {
            
            let gridId = $(this).data("feDebug");
            this.feDebugCounter = this.feDebugCounter || 0;
            if (this.feDebugCounter == 0) console.log("click this question mark 5 times to force show debug info for this grid.");
            
            if (++this.feDebugCounter > 5) {
                $('[data-fe-debug-dude="' + gridId + '"]').show();
                Cookies.set("fe-debug", true, { expires: 15 });
            }
        });
        $(function () {
            if (Cookies.get("fe-debug")) $('[data-fe-debug-dude]').show();
        });
        fe.doc.on("click", "[data-fe-debug-toggle]", function(e){
            if (e.ctrlKey) { Cookies.remove("fe-debug"); $(this).parent().hide(); }
            else $(this).find('+ div').toggle();
        });
js;
        $jsEdit = <<<js
        
        // handles click on rows to take to edit/detail page.
        fe.doc.on("click", "[data-edit-row]", function(e) {
            let self = $(this);
            let index1 = self.data("editRow");
            let formId = self.data("editForm");
            let form = $('#formular');
            let newWindow = fe.e.table.newWindow;
            
            //Return if alt or shift keys are used, or if the cell contains an editable cell
            if (e.altKey || e.shiftKey || self.has('.edittablecell').length) return;
            
            newWindow = newWindow || e.ctrlKey;
            
            $('#index1value').removeAttr("name"); // it is in the query string so we don't need it here.
            $('#formularid').removeAttr("name"); // same as above.
           
            form.attr("action", form.attr("action") + "&index1value=" + encodeURIComponent(index1) + "&form=" + formId);
            
            $('#navi').val('EDIT');
            
            if (newWindow) {
                form.attr("target", "_blank");
            }
            
            form.submit();
            
            if (newWindow) {
                form.removeAttr("target");
                window.keydetect_ctrl = false;
            }
        });
js;
        $jsDelete = <<<js
        
        // deleting rows
        fe.doc.on("click", "[data-delete-row]", function(e) {
            let self = $(this);
            let id = self.data("deleteRow");
            let deleteId = self.data("deleteId");
            
            fe.e.table.openDeleteDialog(id, deleteId, "DELETE");
        });
        fe.doc.on("click", "[data-delete-kenn-row]", function(e) {
            let self = $(this);
            let id = $("#" + self.data("deleteKennRow")).val();
            let kennHidden = self.data("kennHidden");
            let deleteId = self.data("deleteId");
            
            if (!id) return;
            
            fe.e.table.openDeleteDialog(id, deleteId, "DELETEKENNZEICHEN1", function(){
                $("#" + kennHidden).val('');
            });
        });
        // only called by the two functions above.
        fe.e.table.openDeleteDialog = function(id, deleteId, navi, cb) {
            $("[data-delete-dialog]").dialog({
                resizable: false,
                height: 140,
                modal: true,
                buttons: {
                    "Delete item": function() {
                        $("#index1value").val(id);
                        $("#navi").val(navi);
                        $("#elementiddelete").val(deleteId);
                        if (cb) cb();
                        $("#formular").submit();
                    },
                    "Cancel": function() {
                        $(this).dialog("close");
                    }
                }
            });
        };
js;
        $jsAjax = <<<js
        
        fe.doc.on("click", "div.switchAjaxRow div.switchAjaxRow_open", function (e) {
            var box = $(this).parent().parent();
            box.find('div.switchAjaxRow_open').css('display','none');
            box.find('div.switchAjaxRow_close').css('display','block');
            box.find('div.switchAjaxRow_content').css('display','block');
            /*make ajax call*/
        });
        fe.doc.on("click", "div.switchAjaxRow div.switchAjaxRow_close", function (e) {
            var box = $(this).parent().parent();
            box.find('div.switchAjaxRow_open').css('display','block');
            box.find('div.switchAjaxRow_close').css('display','none');
            box.find('div.switchAjaxRow_content').css('display','none');

        });
        
        fe.doc.on("click", "[data-table-refresh]", function (e) {
            let self = $(this);
            let options = self.data("tableRefresh") || {};
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            if (gridId && uniqueGridId) {
                fe.e.table.ajax(options, gridId, uniqueGridId);
            } else {
                console.error("table id not present.");
            }
        });

        fe.doc.on("change", "[data-table-kenn-select]", function (e) {
            let self = $(this);
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            $('#kennzeichen1value').val(self.val());
            $('#' + uniqueGridId + 'kennzeichen1').val(self.val());
            $('#' + uniqueGridId + 'orderby').val('');
            $('#' + uniqueGridId + 'orderbydirection').val('');
            $('#' + uniqueGridId + 'page').val('');
            $('#' + uniqueGridId + 'where').val('');
            fe.e.table.ajax({}, gridId, uniqueGridId);
        });
        
        fe.doc.on("click", "[data-table-order-by-browser]", function (e) {
            
            $('span[data-table-order-by-browser]').removeClass('ui-state-hover');
            
            let self = $(this);
            self.addClass('ui-state-hover');
            
            let fieldName = self.data("tableOrderByBrowser");
            let direction = self.data("tableOrderByDir");
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            let fieldIndex = self.data("tableOrderByIndex");
            let fieldType = self.data("tableOrderByType");
            
            $('#' + uniqueGridId + 'orderby').val(fieldName);
            $('#' + uniqueGridId + 'orderbydirection').val(direction);
            
            try {
                tableSort(gridId, fieldIndex, direction, fieldType);
            }
            catch(err) {}
            
            fe.e.table.ajaxMessage({}, gridId, uniqueGridId);
        });
        
        //html id of the table, numeric index of the column
        /**
        * @param sTableId : html id of the table which should sort
        * @param iColumnNr : numeric index of the column
        * @param sSortType : 'string', 'integer', 'float', 'currency'
        */
        function tableSort(sTableId, iColumnNr, sDirection='ASC', sSortType='string')
        {

            let table, rows, switching, i, x, y, shouldSwitch, iRowStart;
            table = document.getElementById(sTableId);
            switching = true;
            
            iRowStart=0;
            rows = table.getElementsByTagName("TR");
            for (i = 0; i < (rows.length - 1); i++) 
            {
                if(rows[i].classList.contains("ui-state-default"))
                {
                    iRowStart=i;
                    break;
                }
            }
            
            /* nothing to sort */
            if(iRowStart==0 || iRowStart == (rows.length -1))
                return;
            
            /*Make a loop that will continue until no switching has been done:*/
            while (switching) {
                //start by saying: no switching is done:
                switching = false;
                rows = table.getElementsByTagName("TR");
                /*Loop through all table rows (except the first, which contains table headers):*/
                for (i = iRowStart; i < (rows.length - 1); i++) 
                {
                    //start by saying there should be no switching:
                    shouldSwitch = false;
                    /*Get the two elements you want to compare, one from current row and one from the next:*/
                    x = rows[i].getElementsByTagName("TD")[iColumnNr];
                    y = rows[i + 1].getElementsByTagName("TD")[iColumnNr];
                    
                    //check if the two rows should switch place:
                    let value_x, value_y;
                    value_x = x.textContent || x.innerText || "";
                    value_x = value_x.toLowerCase();
                    value_y = y.textContent || y.innerText || "";
                    value_y = value_y.toLowerCase();
                    
                    if(sSortType=='integer')
                    {
                        value_x = parseInt(value_x);
                        if(value_x==NaN) value_x = 0;
                        
                        value_y = parseInt(value_y);
                        if(value_y==NaN) value_y = 0;
                    }
                    if(sSortType=='float')
                    {
                        value_x = value_x.replace(',','').replace('$','').replace('%','');
                        value_x = value_x.trim();
                        value_x = parseFloat(value_x);
                        if(value_x==NaN) value_x = 0;
                        
                        value_y = value_y.replace(',','').replace('$','').replace('%','');
                        value_y = value_y.trim();
                        value_y = parseFloat(value_y);
                        if(value_y==NaN) value_y = 0;
                    }
                    if (sDirection=='ASC' && value_x > value_y) {
                        //if so, mark as a switch and break the loop:
                        shouldSwitch = true;
                        break;
                    }
                    else if(sDirection=='DESC' && value_x < value_y) {
                        //if so, mark as a switch and break the loop:
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    /*If a switch has been marked, make the switch and mark that a switch has been done:*/
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
            
            rows = table.getElementsByTagName("TR");
            for (i = iRowStart; i < (rows.length - 1); i++) 
            {
                rows[i].classList.remove("roweven");
                rows[i].classList.remove("rowodd");
                
                if(i % 2 == 0)
                    rows[i].classList.add("roweven");
                else
                    rows[i].classList.add("rowodd");
            }
            
        }
                
        fe.doc.on("click", "[data-table-order-by]", function (e) {
            let self = $(this);
            let fieldName = self.data("tableOrderBy");
            let direction = self.data("tableOrderByDir");
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            
            $('#' + uniqueGridId + 'orderby').val(fieldName);
            $('#' + uniqueGridId + 'orderbydirection').val(direction);
            
            fe.e.table.ajax({}, gridId, uniqueGridId);
        });

        fe.doc.on("change", "[data-table-select-page]", function (e) {
            let self = $(this);
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            let value = self.val();
            
            $("#" + uniqueGridId + "page").val(value);
            
            fe.e.table.ajax({}, gridId, uniqueGridId);
        });
        
        fe.doc.on("click", "[data-table-btn-search]", function (e) {
            let self = $(this);
            let options = self.data("tableBtnSearch") || {};
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            
            fe.e.table.search(null, options, gridId, uniqueGridId);
        });
        
        fe.e.table.search = function (e, options, gridId, uniqueGridId) {
            $('#' + uniqueGridId + 'where').val("1");
            $('#' + uniqueGridId + 'page').val("0");
            
            fe.e.table.ajax(options, gridId, uniqueGridId);
            if (e) e.preventDefault();
            return false;
        };
        
        fe.e.table.ajax = function(options, gridId, uniqueGridId) {
    
            /* abort all current running ajax calls */
            var fLen = window.ajaxRequest.length;
            for (i = 0; i < fLen; i++) {
                try 
                {
                    window.ajaxRequest[i].abort();
                }
                catch(e)
                {}
            }
            
            
            var param = "project=" + $("#fe-project").val()
                + "&elementid=" + gridId
                + "&elementfunction=getInterpreterRenderAjax"
                + "&" + uniqueGridId + "orderby=" + $("#" + uniqueGridId + "orderby").val()
                + "&" + uniqueGridId + "orderbydirection=" + $("#" + uniqueGridId + "orderbydirection").val()
                + "&" + uniqueGridId + "page=" + $("#" + uniqueGridId + "page").val()
                + "&{$hsConfig->getInterpreterParameterGet()}"
                + "&" + uniqueGridId + "kennzeichen1=" + $("#select" + uniqueGridId + "kennzeichen1").val();
    
            //If the control is now visible, prevent hide it on the response.
            if($("#element" + gridId).css("display")!=="none"){
                param += "&visible=1";
            }
            
            if ($("#" + uniqueGridId + "where").length > 0) {
                param += "&" + uniqueGridId + "where=" + $("#" + uniqueGridId + "where").val();
            }
    
            if ($("#" + uniqueGridId + "wheretable").length > 0) {
                
                var data = $("#" + uniqueGridId + "wheretable").find("select, textarea, input")
                    .serialize()
                    .replace(/=(&|$)/g, "=#CP_EMPTY_STRING_FIX#&"); // look for empty strings and make them into a tag, BASE php is broken and removes empty strings from request.
                
                param += "&" + data;
            }
    
            $("#ajaxcontent" + gridId).css("opacity","0.5");
            $.ajax({
                type: "POST",
                url: fe.e.table.ajaxUrl,
                data: param,
                success: function(data) {
                    $("#ajaxcontent" + gridId).html(data);
                },
                error:function(){
                  alert("There was an error trying to get the result set.");  
                },
                complete: function() {
                    $("#ajaxcontent" + gridId).css("opacity","1.0");
                }
            });
        };
        fe.e.table.ajaxMessage = function(options, gridId, uniqueGridId) {
    
            var param = "project=" + $("#fe-project").val()
                + "&elementid=" + gridId
                + "&elementfunction=getInterpreterRenderAjaxMessage"
                + "&" + uniqueGridId + "orderby=" + $("#" + uniqueGridId + "orderby").val()
                + "&" + uniqueGridId + "orderbydirection=" + $("#" + uniqueGridId + "orderbydirection").val()
                + "&" + uniqueGridId + "page=" + $("#" + uniqueGridId + "page").val()
                + "&{$hsConfig->getInterpreterParameterGet()}"
                + "&" + uniqueGridId + "kennzeichen1=" + $("#select" + uniqueGridId + "kennzeichen1").val();
    
            //If the control is now visible, prevent hide it on the response.
            if($("#element" + gridId).css("display")!=="none"){
                param += "&visible=1";
            }
            
            if ($("#" + uniqueGridId + "where").length > 0) {
                param += "&" + uniqueGridId + "where=" + $("#" + uniqueGridId + "where").val();
            }
    
            if ($("#" + uniqueGridId + "wheretable").length > 0) {
                
                var data = $("#" + uniqueGridId + "wheretable").find("select, textarea, input")
                    .serialize()
                    .replace(/=(&|$)/g, "=#CP_EMPTY_STRING_FIX#&"); // look for empty strings and make them into a tag, BASE php is broken and removes empty strings from request.
                
                param += "&" + data;
            }
    
            /*$("#ajaxcontent" + gridId).css("opacity","0.5");*/
            $.ajax({
                type: "POST",
                url: fe.e.table.ajaxUrl,
                data: param,
                success: function(data) {
                    /*$("#ajaxcontent" + gridId).html(data);*/
                },
                error:function(){
                  /*alert("There was an error trying to get the result set.");  */
                }
            });
            
        };
js;
        $jsSearch = <<<js

        fe.e.table.initSearch = function() {
            $('.table-wherebutton').button();
            // initialize date pickers.
            $('[data-fe-where][data-fe-type="date"]').datepicker({ dateFormat:'yy-mm-dd' });
           
            // behaviour of the checkboxes that remember user selection.
            let co = { expires: 15 }; // cookie options
            $("[data-fe-cookiefor]").each(function(){
                let self = $(this); // to be able to reference the cookie checkbox inside callbacks.
                let cookieFor = self.data("feCookiefor"); // name of the input this cb is bound to.
                let inputFor = $('[data-fe-where][name="' + cookieFor + '"]'); // input this cb is bound to.
                let isCheckbox = inputFor.is('[type="checkbox"]');
                // called to set or remove the cookie value on inputs
                let updateCookie = (cookieFunction) =>
                    // cookieFunction can be Cookies.set or Cookies.remove
                    Cookies[cookieFunction]( 
                        // checkboxes behave differently, only save true for checked.
                        cookieFor, isCheckbox ? inputFor.is(":checked") : inputFor.val(), co
                    );
                let cookieName = self.attr("name");
                self.change(function(){ // when the cb is activated, save the cookie, on deactivate remove the cookie.
                    let cookieFunction = self.is(":checked") ? "set" : "remove";
                    Cookies[cookieFunction](cookieName, true, co);
                    updateCookie(cookieFunction);
                }).prop("checked", !!Cookies.get(cookieName));
                inputFor.change(function(){ // when input value changes, update the cookie.
                    if (self.is(":checked")) updateCookie("set");
                });
            });
        };
        
        // on press enter when inside a text box
        fe.doc.on("keypress", "[data-fe-where][type=text]", function (e) {
            let self = $(this);
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            if (e.which === 13) {
                $(this).blur(); // to trigger the onchange on the inputs, so cookies are handled with js below.
                fe.e.table.search(e, {}, gridId, uniqueGridId);
            }
        });
        
        fe.doc.on("change", "select[data-fe-where], [data-fe-where][type=checkbox]", function (e) {
            let self = $(this);
            let gridId = self.data("tableId");
            let uniqueGridId = self.data("tableUniqueId");
            fe.e.table.search(e, {}, gridId, uniqueGridId);
        });
js;
        $jsEditMode = <<<js

        // Edit Mode start

        fe.e.table.getEditModeData = function (self) {
            // simplifies the extracting part of the data attributes
            self = $(self);
            return {
                gridId: self.data("tableId"),
                uniqueGridId: self.data("tableUniqueId"),
                rowId: self.data("tableRowId"),
                colIndex: self.data("tableColIndex"),
                rowIndex: self.data("tableRowIndex"),
                cellValues: self.data("tableCellValues"),
                value: self.val()
            };
        };

        fe.doc.on("keydown", "textarea[data-table-edit-mode]", function (e) {
            let d = fe.e.table.getEditModeData(this);
            if (keyPressed === 13) {
                fe.e.table.editModeChange(d.rowId, d.colIndex, d.rowIndex, d.value, d.gridId, d.uniqueGridId, d.cellValues);
                e.preventDefault();
            }
            if(keyPressed === 27) {
                fe.e.table.editModeCancel(d.rowId, d.colIndex, d.rowIndex, d.uniqueGridId, d.cellValues, d.cellValues);
                e.preventDefault();
            }
        });

        fe.doc.on("blur", "textarea[data-table-edit-mode]", function (e) {
            let d = fe.e.table.getEditModeData(this);
            fe.e.table.editModeChange(d.rowId, d.colIndex, d.rowIndex, d.value, d.gridId, d.uniqueGridId, d.cellValues);
        });

        fe.doc.on("focus", "textarea[data-table-edit-mode]", function (e) {
            this.select();
            this.onmouseup = function() {
                this.onmouseup = null;
                return false;
            };
        });

        fe.doc.on("change", "select[data-table-edit-mode]", function (e) {
            let d = fe.e.table.getEditModeData(this);
            fe.e.table.editModeChange(d.rowId, d.colIndex, d.rowIndex, d.value, d.gridId, d.uniqueGridId, d.cellValues);
        });

        fe.doc.on("blur", "select[data-table-edit-mode]", function (e) {
            let d = fe.e.table.getEditModeData(this);
            fe.e.table.editModeCancel(d.rowId, d.colIndex, d.rowIndex, d.uniqueGridId, d.cellValues);
        });

        fe.doc.on("click", '[data-table-edit-mode="edit"]:not(.edit)', function (e) {
            let d = fe.e.table.getEditModeData(this);
            let cell = $(this);
    
            var param = "project={$hsConfig->getProjectName()}";
            param += "&elementid=" + d.gridId;
            param += "&elementfunction=getInterpreterRenderCellEditMode";
            param += "&" + $("#formular").serialize();
            param += "&current_column_index=" + encodeURIComponent(d.colIndex);
            param += "&current_row_index=" + encodeURIComponent(d.rowIndex);
            param += "&current_index=" + encodeURIComponent(d.rowId);
            param += "&current_cell_values=" + encodeURIComponent(d.cellValues);
    
            $.ajax({
                type: "POST",
                url: fe.e.table.ajaxUrl,
                data: param
            }).success(function(data){
                if(data) {
                    cell.html(data).addClass("edit");
    
                    if (cell.find("textarea")) {
                        cell.find("textarea").focus();
                    }
                    if (cell.find("select")) {
                        cell.find("select").focus();
                    }
                }
            });
        });

        fe.e.table.editModeCancel = function(index1, colIndex, rowIndex, uniqueGridId, cellValues) {
            let editDiv = '[data-table-edit-mode="edit"]'
                + '[data-table-unique-id="' + uniqueGridId + '"]'
                + '[data-table-row-index="' + rowIndex + '"]'
                + '[data-table-col-index="' + colIndex + '"]';
            $(editDiv).html("").removeClass("edit");
        };
        
        fe.e.table.editModeChange = function(index1, colIndex, rowIndex, value, gridId, uniqueGridId, cellValues) {
            // cancel if it is the same value
            let editDiv = $('[data-table-edit-mode="edit"]'
                + '[data-table-unique-id="' + uniqueGridId + '"]'
                + '[data-table-row-index="' + rowIndex + '"]'
                + '[data-table-col-index="' + colIndex + '"]');
            if(editDiv.html() === value) {
                fe.e.table.editModeCancel(index1, colIndex, rowIndex, uniqueGridId, cellValues);
                return;
            }
    
            var param = "project={$hsConfig->getProjectName()}";
            param += "&elementid=" + gridId;
            param += "&elementfunction=getInterpreterRenderCellEditModeSave";
            param += "&";
            param += $("#formular").serialize();
            param += "&current_column_index=" + encodeURIComponent(colIndex);
            param += "&current_row_index=" + encodeURIComponent(rowIndex);
            param += "&current_index=" + encodeURIComponent(index1);
            param += "&current_value=" + encodeURIComponent(value);
            param += "&current_cell_values=" + encodeURIComponent(cellValues)
    
            $.ajax({
                type: "POST",
                url: fe.e.table.ajaxUrl,
                data: param
            }).success(function(data) {
                editDiv.html("").removeClass("edit");
                editDiv.parent().find('[data-table-edit-mode="content"]').html(data);
            });
        };
js;
        // concatenate all of the js and return it.
        $js = "\n$js\n$jsDebug\n$jsEdit\n$jsDelete\n$jsAjax\n$jsSearch\n$jsEditMode\n";

        return $js;
    }

    /**
     * Creates a subReport section for tables
     *
     * @return string
     */
    protected function _addControllerCallbackSection()
    {
        $html = parent::getEditorProperty_Line("Controller Callbacks", true);
        $html .= parent::getEditorProperty_Label("This section helps to define callbacks onto cells, for example to get a report.<br><br>
<b>Example:</b> Manufacturers view has columns with a count of CoycoArticles and VendorArticles (all, active, non eol, etc.). 
We want to have a list of the elements that belongs to that count.<br><br>
<b>Usage:</b> ColumnName=>Controller@index:ui-icon-link<br><br>
- ColumnName: The name of the column in the table, it can be also the column number (First column is 1).<br>
- Controller: Class name located on controller's folder of formedit project. (module_name/formedit/controllers)<br>
- index: Callable method on <i>Controller</i><br>
- ui-icon-link (optional): jQuery ui icon, link is the value by default");
        $html .= parent::getEditorProperty_Textarea('List of Cell Exports (separated by pipes)', 'controller-callback');

        return $html;
    }


    protected $_aControllerCallbacks;

    /**
     * Convert the param
     *
     * @return array
     */
    protected function _getControllerCallbacks()
    {
        if ($this->_aControllerCallbacks === null) {
            $hsConfig             = getHsConfig();
            $aControllerCallbacks = [];
            $sControllerCallback  = $this->property['controller-callback'];

            if ($sControllerCallback) {
                //Get all the declarations to callbacks for columns
                $srColumns = explode('|', $sControllerCallback);

                //On each one...
                foreach ($srColumns as $srColumn) {
                    //If has an arrow...
                    if (strpos($srColumn, "=>") !== false) {

                        //Split the column from the Callback action
                        $srValues = explode('=>', $srColumn);

                        //Should be two values
                        if (count($srValues) == 2) {
                            $srField = trim($srValues[0]);//The column that will make the callback
                            $srValue = trim($srValues[1]);//The callback

                            //If both are defined...
                            if ($srField != "" && $srValue != "") {
                                //If the callback has an 'at' char...
                                if (strpos($srValue, "@") !== false) {
                                    //Split Controller and Method
                                    $srValue = explode('@', $srValue);

                                    $_cl  = $srValue[0];//Controller class
                                    $_fnc = $srValue[1];//Method of this controller
                                } else {
                                    //If is not an 'at' on the string, call the index method.
                                    $_cl  = $srValue;
                                    $_fnc = 'index:ui-icon-link';//This method is defined by default on the Controller master class.
                                }

                                //Search if an icon was defined on the statement
                                if (strpos($_fnc, ":") !== false) {
                                    //Split the icon from the function
                                    $_fnc = explode(':', $_fnc);

                                    $_icon = $_fnc[1];//Icon to place
                                    $_fnc  = $_fnc[0];//Function to call
                                } else {
                                    $_icon = 'ui-icon-link';
                                }

                                //Get the relative path to formedit folder of the module
                                $clPath = $hsConfig->getProjectRelativePath() . DIRECTORY_SEPARATOR . 'controllers';

                                //Add the relative path to the controller
                                $_cl = $clPath . DIRECTORY_SEPARATOR . $_cl;

                                //Create the object that will be pushed to of controller callbacks array
                                $callback                       = new stdClass();
                                $callback->cl                   = $_cl;
                                $callback->fnc                  = $_fnc;
                                $callback->icon                 = $_icon;
                                $aControllerCallbacks[$srField] = $callback;
                            }
                        }
                    }
                }
            }
            $this->_aControllerCallbacks = $aControllerCallbacks;
        }

        return $this->_aControllerCallbacks;
    }

    protected function _getCheckboxListenerJavascript()
    {
        return <<<js
var checkboxRowIndexStart = null;
var jq_checkboxRows = [];

$(document).ready(function(){
    // Get all the checkboxes which belongs with that name.
    jq_checkboxRows = $("input:checkbox[name='{$this->id}[]']"); //bulk_delete[]
    jq_checkboxRowsBulkDelete = $("input:checkbox[name='bulk_delete[]']");
    
    jq_checkboxRows = $.merge(jq_checkboxRows,jq_checkboxRowsBulkDelete);
    
    //Add a clicklistener to all of them.
   $(jq_checkboxRows).on('click',function(evt){
       let thisCheckboxRowIndex = $(jq_checkboxRows).index(this);
       if(evt.shiftKey){//If the click is using also the Shift Key.
           $.each(jq_checkboxRows, function(index, item){
               //Check the boxes below or above the first CB clicked
               if((checkboxRowIndexStart<thisCheckboxRowIndex &&  index>=checkboxRowIndexStart && index<=thisCheckboxRowIndex)
               || (checkboxRowIndexStart>thisCheckboxRowIndex && index<=checkboxRowIndexStart && index>=thisCheckboxRowIndex)){
                   $(item).prop('checked', true);
               }
           });
       }else if(this.checked){
           checkboxRowIndexStart = thisCheckboxRowIndex;
       }
   });
});
js;

    }
}