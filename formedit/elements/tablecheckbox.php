<?php
include_once(__DIR__ . '/table.php');

class tablecheckbox extends table
{
    var $name = "tablecheckbox";
    var $editorname = "Grid Checkbox";
    var $editordescription = 'Shows a Grid Checkboxes (multiselect).';

    public function getInterpreterRender()
    {
        $e = parent::getInterpreterRender();

        $scripts = <<<js
function checkall{$this->uniqueGridId}() {
    $(".{$this->uniqueGridId}checkall_target").prop("checked", $("#{$this->uniqueGridId}checkall").prop("checked"));
}
js;
        $e .= "<script>\n$scripts\n</script>";
        return $e;
    }

    protected function getTable()
    {
        $html = parent::getTable();

        // new feature in this class!
        $checkboxSelectAll = "<th style=width:10px><input type=checkbox id='{$this->uniqueGridId}checkall' onclick='checkall{$this->uniqueGridId}();' value='1'></th>";

        $html = str_replace("<!--fe_tf_before_tds-->", $checkboxSelectAll, $html);

        return $html;
    }

    public function getHtmlRow($row, $fields, $formularid_edit, $i)
    {
        $htmlRow = parent::getHtmlRow($row, $fields, $formularid_edit, $i);

        $checkBoxPart = "<td style='width:10px;'><input type='checkbox' class='{$this->uniqueGridId}checkall_target' name='$this->id[]' value='$row[0]'></td>";

        $htmlRow = str_replace("<!--fe_tf_before_tds-->", $checkBoxPart, $htmlRow);

        return $htmlRow;
    }

    /**
     * Creates the form used on formedit to change the properties for the form.
     *
     * @return string
     */
    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();

        // region Sql Statement

        $html .= parent::getEditorProperty_Line("Sql statement");

        $html .= parent::getEditorProperty_Textarea("SQL-statment (first column must be a index, second column must be the foreign index to the groups table) (Variables: #WHERE# #HAVING# #ORDERBY# #LIMIT#) (Tipp: SQL_CALC_FOUND_ROWS)", 'sqlstring');
        $html .= parent::getEditorProperty_Textarea("SQL-statment that returns the count from the table (Variables: #WHERE#) (Tipp: SELECT FOUND_ROWS())", 'sqlstringcount');
        $html .= parent::getEditorProperty_Textbox("Rows, that gets displayed in the grid", 'limitoffset');
        $html .= parent::getEditorProperty_Textarea("Where-condition (Variables: #INDEX1# #INDEX2#)", 'wherefixed');
        $html .= parent::getEditorProperty_Textbox("Columnname and orderdirection from the permanent sortorder", 'orderbyfixed');
        $html .= parent::getEditorProperty_Textbox("Columnname from the standardorder that can changed by the user", 'orderby');
        $html .= parent::getEditorProperty_Selectbox("Direction from the standardorder that can changed by the user", 'orderbydirection', array('ASC' => 'ASC', 'DESC' => 'DESC'), 'ASC');
        $html .= parent::getEditorProperty_Textbox("Columns names seperate by | which can not sort", 'unsortable', '');

        // endregion

        // region Search

        $html .= parent::getEditorProperty_Line("Search", true);

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
        $html .= parent::getEditorProperty_Checkbox("Should a edit button displayed?", 'showbuttonedit', '1');
        $html .= parent::getEditorProperty_SelectboxFormulare("Which form should get loaded, after click the edit button? (ID Form)", 'formularid_edit');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Should a delete button displayed?", 'showbuttondelete', '1');
        $html .= parent::getEditorProperty_Textbox("Tablename for the deletefunction", 'deletetable');
        $html .= parent::getEditorProperty_Textbox("Columnname with the index for the deletefunction", 'deletecolindex');
        $html .= parent::getEditorProperty_Checkbox("Should a delete button only display with following condition?", 'showbuttondelete_condition', '0');
        $html .= parent::getEditorProperty_Textarea("SQLString return 1 if the delete button should display, all other values the delete button wont be display. Variabes: #INDEX1#", 'showbuttondelete_condition_sql');

        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Should a archive button displayed?", 'showbuttonarchive', '0');
        $html .= parent::getEditorProperty_Textarea("SQLString condition if the button is enabled. If the sqlstatment returns 1 it is enabled else disabled. Variabes: #INDEX1#", 'archivsqlcondition');
        $html .= parent::getEditorProperty_Textbox("Text of the button", 'buttonarchivetext', 'Archiv');
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
        <!--TYPE compressed = extract data<br>
        TYPE compressedtext = extract data and convert new line into html breaks<br>
        TYPE compressedhtmltext = extract data, convert special html code to text and convert new line into html breaks<br>-->
        TYPE sql::select if(type=mail,'compressedtext','compressedhtmltext') from mytable where cpid='#INDEX1#' = can choose the type by a sql statment<br>
        Example:
        Order Number=number|Total=currency|Description=text
        ");
        $html .= parent::getEditorProperty_Checkbox("Format columns", 'formatcolumns', '0');
        $html .= parent::getEditorProperty_Textarea("Format", 'format');

        // region Other Settings
        $html .= parent::getEditorProperty_Line("Other settings", true);

        $html .= parent::getEditorProperty_Checkbox("Use Row text color?", 'showcolor', '0');
        $html .= parent::getEditorProperty_Textarea("Every row executes following sql statement. The retunvalue must be a colorvalue in CSS. Variabes: #INDEX1#", 'colorsql');
        $html .= parent::getEditorProperty_Textarea("Legend where you can describe what the colors mean: (you can use html, the line break will automaticly added (&lt;br&gt;)", 'colorlegend');



        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textbox("Columnwidth in px with | separated", 'colwidth');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Fix height from the table", 'fixheight', '0');
        $html .= parent::getEditorProperty_Checkbox("Fix width from the table, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Export as CSV file", 'exportcsv', '0');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textbox("Cache duration (0 means no cache, default is 1) in hours.", 'cacheduration', '1');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Debug-modus", 'debugmode', '0');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorPropertyFooter(true, false, false, true, true, true, true, true);

        // endregion

        return $html;
    }
}
