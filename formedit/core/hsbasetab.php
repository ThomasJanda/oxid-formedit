<?php

class basetab extends baseelement
{
	public function __construct()
	{
	    parent::__construct();
	    $this->createid();
	}
    public function createid()
    {
        $this->property['id']="tab".uniqid("");
    }

    /**
     * @return string
     */
	public function getSQL()
	{
	    $table=$this->property['table'];
	    $autowert_feld=$this->property['colindex'];
	    return "CREATE TABLE `$table` (`$autowert_feld` VARCHAR(50) NULL , PRIMARY KEY ( `$autowert_feld` ));";
	}
    /**
     * @return string
     */
	public function getTabCustomerId()
	{
	    return $this->property['customerid'];
	}
    /**
     * @return string
     */
	public function getTabName()
	{
	    return trim($this->property['bezeichnung']);
	}
    /**
     * @return string
     */
	public function getColIndex()
    {
        return trim($this->property['colindex']);
    }
    /**
     * @return string
     */
	public function getTabId()
	{
	   return $this->property['id'];
	}
    /**
     * @return string
     */
	public function getTableName()
	{
	    return $this->property['table'];
	}

	/*
    public function interpreterNew($index)
    {
        $table = $this->property['table'];
        $colIndex = $this->property['colindex'];
        if($table != "" && $colIndex != "") {
            $this->_createRow($table, $colIndex, $index);
        }
    }
    */

    /**
     * @param string $index
     * @param \basecontrol[] $aElements
     */
    public function interpreterNew($index, $aElements)
    {
        $table = $this->property['table'];
        $colIndex = $this->property['colindex'];
        if ($table != "" && $colIndex != "") {

            //find first all foreign key columns which need a value
            $aForeignKeys = [];
            foreach ($aElements as $oe) {
                if (isset($oe->property['foreignkey']) && $oe->property['foreignkey'] == "1") {
                    if ($s = $oe->interpreterSaveNew($table, $colIndex, $index)) {
                        $value = $s['value'];
                        $value = str_replace("#EMPTY#", "", $value);
                        $value = str_replace("'#NULL#'", "null", $value);
                        $aForeignKeys[$s['col']] = $value;
                    }
                }
            }

            $this->_createRow($table, $colIndex, $index, $aForeignKeys);
        }
    }

    /**
     * create new row in a table
     *
     * @param string $sTable
     * @param string $sPrimaryColumn
     * @param string $sPrimaryIndex
     * @param string[] $aForeignKeys
     */
    protected function _createRow($sTable, $sPrimaryColumn, $sPrimaryIndex, $aForeignKeys = [])
    {
        if ($sTable != "" && $sPrimaryColumn != "" && $sPrimaryIndex != "") {

            $oConfig = getHsConfig();
            $aForeignKeys[$sPrimaryColumn] = "'{$oConfig->escapeString($sPrimaryIndex)}'";

            $sColumns = "`".implode("`,`", array_map('trim', array_keys($aForeignKeys)))."`";
            $sValues = implode(",", $aForeignKeys);

            /** @var mysqli $db */
            $sSql = "insert into `{$sTable}` ({$sColumns}) values ({$sValues})";
            if ($this->property['debugmode'] == "1") {
                echo $sSql."<br>";
            }
            $oConfig->executeNoReturn($sSql);
        }
    }

    /**
     * @deprecated
     * @param $index
     * @param $col
     * @param $value
     */
    /*
    public function interpreterUpdate($index, $col, $value)
    {

        if($this->property['table']!="" && trim($col)!="" && trim($this->property['colindex'])!="")
        {
            $hsconfig=getHsConfig();

            $sqlstring="update `".$this->property['table']."` set `".$col."`=".$value." where `".$this->property['colindex']."`='".$hsconfig->escapeString($index)."'";
            if($this->property['debugmode']=="1")
                echo $sqlstring."<br>";

            $hsconfig->executeNoReturn($sqlstring);
            //mysql_query($sqlstring,$hsconfig->getDbId());
        }
    }
    */

    /**
     * method will call right after the main record was insert into the database with all foreign keys, we only need to add the other values
     * @param string $index
     * @param \basecontrol[] $aElements
     */
    public function interpreterUpdateRecordNew($index, $aElements)
    {
        $table = $this->property['table'];
        $colIndex = $this->property['colindex'];
        if ($table != "" && $colIndex != "") {

            $aColumns = [];
            foreach ($aElements as $oe) {
                if ( ! isset($oe->property['foreignkey']) || $oe->property['foreignkey'] != "1") {
                    if ($s = $oe->interpreterSaveNew($table, $colIndex, $index)) {
                        $value = $s['value'];
                        $value = str_replace("#EMPTY#", "", $value);
                        $value = str_replace("'#NULL#'", "null", $value);
                        $s['value'] = $value;
                        $aColumns[] = $s;
                    }
                }
            }

            //update record
            $this->_interpreterUpdateRecord($index, $aColumns);
        }
    }

    /**
     * method will call to update the record
     * @param string $index
     * @param \basecontrol[] $aElements
     */
    public function interpreterUpdateRecordEdit($index, $aElements)
    {
        $table = $this->property['table'];
        $colIndex = $this->property['colindex'];
        if ($table != "" && $colIndex != "") {

            $aColumns = [];
            foreach ($aElements as $oe) {
                if ($s = $oe->interpreterSaveEdit($table, $colIndex, $index)) {
                    $value = $s['value'];
                    $value = str_replace("#EMPTY#", "", $value);
                    $value = str_replace("'#NULL#'", "null", $value);
                    $s['value'] = $value;
                    $aColumns[] = $s;
                }
            }

            //update record
            $this->_interpreterUpdateRecord($index, $aColumns);
        }
    }

    /**
     * This method should not be used for inserts because it requires that our database design continues to be restricted
     * to bad patterns, like allow null in columns where we don't want nulls, or to give default values to columns that
     * should not have default values.
     *
     * @param string $index
     * @param array  $aColumnDefinitions : array(
     *                     0 => array(
     *                          'table' => 'TABLENAME',
     *                          'foreignkey' => true|false,
     *                          'col' => 'COLUMNNAME',
     *                          'value' => 'VALUE',
     *                          'element' => \basecontrol
     *                        ),
     *                     1 => array(...),
     *                     )
     */
    protected function _interpreterUpdateRecord($index, array $aColumnDefinitions)
    {
        $oConfig = getHsConfig();

        $sMainTable = $this->property['table'];
        $sMainPrimaryColumn = trim($this->property['colindex']);

        //split up columns because sub tables
        $aTableColumns = [];
        $aTableColumns[strtolower($sMainTable)]['ismain'] = 1;
        $aTableColumns[strtolower($sMainTable)]['table'] = $sMainTable;
        $aTableColumns[strtolower($sMainTable)]['primarycolumn'] = $sMainPrimaryColumn;
        $aTableColumns[strtolower($sMainTable)]['primarykey'] = $index;
        $aTableColumns[strtolower($sMainTable)]['foreignkey'] = "";
        $aTableColumns[strtolower($sMainTable)]['foreigncolumn'] = "";
        $aTableColumns[strtolower($sMainTable)]['columnsdefinition'] = [];

        foreach ($aColumnDefinitions as $aColumnDefinition) {
            $sTable = $aColumnDefinition['table'];

            if ( ! isset($aTableColumns[strtolower($sTable)])) {
                //unknown table => subtable
                $aSubTable = $this->_getSubTableData($sTable);
                if ($aSubTable === false) {
                    die("NO SUBTABLE DEFINITION FOUND FOR ".$aColumnDefinition['table'].".".$aColumnDefinition['col']);
                }
                if ( ! isset($aTableColumns[strtolower($aSubTable['tablename'])])) {
                    $aTableColumns[strtolower($aSubTable['tablename'])]['ismain'] = 0;
                    $aTableColumns[strtolower($aSubTable['tablename'])]['table'] = $aSubTable['tablename'];
                    $aTableColumns[strtolower($aSubTable['tablename'])]['primarycolumn'] = $aSubTable['primarycolumn'];
                    $aTableColumns[strtolower($aSubTable['tablename'])]['foreigncolumn'] = $aSubTable['foreigncolumn'];
                    $aTableColumns[strtolower($aSubTable['tablename'])]['primarykey'] = "";
                    $aTableColumns[strtolower($aSubTable['tablename'])]['foreignkey'] = $index;
                    $aTableColumns[strtolower($aSubTable['tablename'])]['columnsdefinition'] = [];
                }
            }
            $aTableColumns[strtolower($sTable)]['columnsdefinition'][md5(strtolower($aColumnDefinition['table']."."
                .$aColumnDefinition['col']))]
                = $aColumnDefinition;
        }


        //main table can always update because must present in the update function
        foreach ($aTableColumns as $aTableData) {
            if ($aTableData['ismain'] == "1") {
                $sqlFields = [];
                foreach ($aTableData['columnsdefinition'] as $aColumnDefinition) {
                    $sqlFields[] = "`".$aColumnDefinition['col']."` = ".$aColumnDefinition['value'];
                }

                if ($sqlFields) {
                    $imploded = implode(",\n", $sqlFields);
                    $sSql = "update `{$aTableData['table']}` set
                    {$imploded}
                    where `{$aTableData['primarycolumn']}` = '{$oConfig->escapeString($aTableData['primarykey'])}'";

                    if ($this->property['debugmode']) {
                        echo "<pre>$sSql</pre>";
                    }

                    $oConfig->executeNoReturn($sSql);
                }
                break;
            }
        }

        //sub table must not present because could create later
        foreach ($aTableColumns as $aTableData) {
            if ($aTableData['ismain'] == "0") {
                //find primary key
                $sPrimaryKey = "";
                if (strtolower($aTableData['primarycolumn']) != strtolower($aTableData['foreigncolumn'])) {
                    $sSql
                        = "select `{$aTableData['primarycolumn']}` from {$aTableData['table']} where `{$aTableData['foreigncolumn']}` = '{$oConfig->escapeString($aTableData['foreignkey'])}'";
                    $sPrimaryKey = $oConfig->getScalar($sSql);

                    if ($sPrimaryKey == "") {
                        //no primary key present, means row does not exists, create a new one
                        $sPrimaryKey = uniqid("");

                        //collect all values from the subtables (all foreign keys to the table, otherwise insert maybe fail)
                        $aColumns = [];
                        $aColumns[$aTableData['foreigncolumn']] = "'".$oConfig->escapeString($aTableData['foreignkey'])."'";
                        foreach ($aTableData['columnsdefinition'] as $aColumnDefinition) {
                            if ($aColumnDefinition['foreignkey']) {
                                $aColumns[$aColumnDefinition['col']] = $aColumnDefinition['value'];
                            }
                        }

                        //create a new row
                        $this->_createRow($aTableData['table'], $aTableData['primarycolumn'], $sPrimaryKey, $aColumns);
                    }
                } else {
                    $sPrimaryKey = $aTableData['foreignkey'];

                    //foreign column is primary column, test if row is present
                    $sSql
                        = "select count(*) from {$aTableData['table']} where `{$aTableData['foreigncolumn']}` = '{$oConfig->escapeString($aTableData['foreignkey'])}'";
                    if ($oConfig->getScalar($sSql) == "0") {
                        //create row first

                        //collect all values from the subtables (all foreign keys to the table, otherwise insert maybe fail)
                        $aColumns = [];
                        foreach ($aTableData['columnsdefinition'] as $aColumnDefinition) {
                            if ($aColumnDefinition['foreignkey']) {
                                $aColumns[$aColumnDefinition['col']] = $aColumnDefinition['value'];
                            }
                        }

                        //create new row
                        $this->_createRow($aTableData['table'], $aTableData['primarycolumn'], $aTableData['foreignkey'], $aColumns);
                    }
                }

                //get all date
                $sqlFields = [];
                foreach ($aTableData['columnsdefinition'] as $aColumnDefinition) {
                    $sqlFields[] = "`".$aColumnDefinition['col']."` = ".$aColumnDefinition['value'];
                }

                //update the other values
                if ($sqlFields) {
                    $imploded = implode(",\n", $sqlFields);
                    $index = $oConfig->escapeString($index);
                    $sSql = "update `{$aTableData['table']}` set
                    {$imploded}
                    where `{$aTableData['primarycolumn']}` = '{$oConfig->escapeString($sPrimaryKey)}'";

                    if ($this->property['debugmode']) {
                        echo "<pre>$sSql</pre>";
                    }

                    $oConfig->executeNoReturn($sSql);
                }
            }
        }


        return;

        /*
        foreach($cols as $col => $value)
        {
            if (!trim($col)) continue;

            if(strpos($col,"|")!==false)
            {
                $aTmp = explode("|",$col);
                $col=$aTmp[1];

                $aSubTable = $this->_getSubTableData($aTmp[0]);
                if($aSubTable===false)
                {
                    die("NO SUBTABLE DEFINITION FOUND FOR ".$col);
                }
                if(!isset($aTableColumns[strtolower($aSubTable['tablename'])]))
                {
                    $aTableColumns[strtolower($aSubTable['tablename'])]['ismain']=0;
                    $aTableColumns[strtolower($aSubTable['tablename'])]['table']=$aSubTable['tablename'];
                    $aTableColumns[strtolower($aSubTable['tablename'])]['primarycolumn']=$aSubTable['primarycolumn'];
                    $aTableColumns[strtolower($aSubTable['tablename'])]['foreigncolumn']=$aSubTable['foreigncolumn'];
                    $aTableColumns[strtolower($aSubTable['tablename'])]['primarykey']="";
                    $aTableColumns[strtolower($aSubTable['tablename'])]['foreignkey']=$index;
                    $aTableColumns[strtolower($aSubTable['tablename'])]['columns']=[];
                }
                $aTableColumns[strtolower($aSubTable['tablename'])]['columns'][$col]=$value;
            }
            else
            {
                $aTableColumns[strtolower($sMainTable)]['columns'][$col]=$value;
            }
        }

        //main table can always update because must present in the update function
        foreach($aTableColumns as $aTableData)
        {
            if($aTableData['ismain']=="1")
            {
                //save the main table first
                $sqlFields = array();
                foreach ($aTableData['columns'] as $col => $value) {
                    $sqlFields[$col] = "`$col` = $value";
                }

                if ($sqlFields) {
                    $imploded = implode(",\n", $sqlFields);
                    $index = $oConfig->escapeString($index);
                    $sSql = "update `{$aTableData['table']}` set
                    {$imploded}
                    where `{$aTableData['primarycolumn']}` = '{$oConfig->escapeString($aTableData['primarykey'])}'";

                    if($this->property['debugmode']) {
                        echo "<pre>$sSql</pre>";
                    }

                    $oConfig->executeNoReturn($sSql);
                }
                break;
            }
        }
        //sub table must not present because could create later
        foreach($aTableColumns as $aTableData)
        {
            if($aTableData['ismain']=="0")
            {
                //save the main table first
                $sqlFields = array();
                foreach ($aTableData['columns'] as $col => $value) {
                    $value = str_replace("#EMPTY#", "", $value);
                    $value = str_replace("'#NULL#'", "null", $value);
                    $sqlFields[$col] = "`$col` = $value";
                }

                //find primary key
                $sPrimaryKey="";
                if(strtolower($aTableData['primarycolumn'])!=strtolower($aTableData['foreigncolumn']))
                {
                    $sSql="select `{$aTableData['primarycolumn']}` from {$aTableData['table']} where `{$aTableData['foreigncolumn']}` = '{$oConfig->escapeString($aTableData['foreignkey'])}'";
                    $sPrimaryKey = $oConfig->getScalar($sSql);

                    if($sPrimaryKey=="")
                    {
                        //no primary key present, means row does not exists, create a new one
                        $sPrimaryKey=uniqid("");
                        $this->_createRow($aTableData['table'], $aTableData['primarycolumn'], $sPrimaryKey);

                        //add foreign key to the table
                        $sSql = "update `{$aTableData['table']}` set
                        `{$aTableData['foreigncolumn']}` = '{$oConfig->escapeString($aTableData['foreignkey'])}'
                        where `{$aTableData['primarycolumn']}` = '{$oConfig->escapeString($sPrimaryKey)}'";

                        if($this->property['debugmode']) {
                            echo "<pre>$sSql</pre>";
                        }

                        $oConfig->executeNoReturn($sSql);
                    }
                }
                else
                {
                    $sPrimaryKey = $aTableData['foreignkey'];

                    //foreign column is primary column, test if row is present
                    $sSql="select count(*) from {$aTableData['table']} where `{$aTableData['foreigncolumn']}` = '{$oConfig->escapeString($aTableData['foreignkey'])}'";
                    if($oConfig->getScalar($sSql)=="0")
                    {
                        //create row first
                        $this->_createRow($aTableData['table'], $aTableData['primarycolumn'], $aTableData['foreignkey']);
                    }
                }

                //update the other values
                if ($sqlFields) {
                    $imploded = implode(",\n", $sqlFields);
                    $index = $oConfig->escapeString($index);
                    $sSql = "update `{$aTableData['table']}` set
                    {$imploded}
                    where `{$aTableData['primarycolumn']}` = '{$oConfig->escapeString($sPrimaryKey)}'";

                    if($this->property['debugmode']) {
                        echo "<pre>$sSql</pre>";
                    }

                    $oConfig->executeNoReturn($sSql);
                }
            }
        }
        */

    }

    /**
     * till now not in use 20190327
     *
     * This method could be used for updates and inserts IF all our formedit projects used primary keys as the column
     * to update records by, but that is not the case, so we cannot use this for updates.
     * @param $index
     * @param array $cols
     */
    /*
    public function interpreterInsertRecord($index, array $cols)
    {
        $db = getHsConfig()->getDbId("mysqli");

        $table = $this->property['table'];
        $indexCol = trim($this->property['colindex']);

        if($table && $indexCol) {

            $sqlCols = array();
            $sqlValues = array();
            foreach ($cols as $col => $value) {
                // if no column, skip input
                if (!trim($col)) continue;

                // parsing the value first, allow for some tokens.
                $value = str_replace("#EMPTY#", "", $value);
                $value = str_replace("'#NULL#'", "null", $value);

                $sqlCols[$col] = "`$col`";
                $sqlValues[$col] = $value;
            }

            if ($sqlCols) {
                $index = getHsConfig()->escapeString($index);

                $implodedCols = implode(", ", $sqlCols);
                $implodedValues = implode(", ", $sqlValues);
                $duplicateImplodedCols = implode(", ", array_map(function ($c) { return "$c = values($c)"; }, $sqlCols));

                $sqlInsertUpdate = "
                  insert into `$table` (`$indexCol`, $implodedCols)
                  values ('$index', $implodedValues)
                  on duplicate key update $duplicateImplodedCols
                ";
                if($this->property['debugmode']) {
                    echo "<pre>$sqlInsertUpdate</pre>";
                }
                $db->query($sqlInsertUpdate);
            }
        }

    }
    */

    /**
     * @param $sTableName
     *
     * @return array|bool
     */
    protected function _getSubTableData($sTableName)
    {
        $sSubTableDefinition = $this->property['subtabledefinition'];
        $aSubTableDefinition = explode("\n", $sSubTableDefinition);
        $aSubTableDefinition = array_map('trim', $aSubTableDefinition);

        $aData = false;
        foreach ($aSubTableDefinition as $sSubTable) {
            if (substr(strtolower($sTableName."|"), 0, strlen($sTableName) + 1) == strtolower($sTableName."|")) {
                $aTmp = explode("|", $sSubTable);
                if (count($aTmp) >= 2) {
                    $aData = [];
                    $aData['tablename'] = $aTmp[0];
                    $aData['primarycolumn'] = $aTmp[1];
                    $aData['foreigncolumn'] = $aTmp[2] ?? $aTmp[1];
                }
            }
        }

        return $aData;
    }

    public function interpreterLoad($index, $col)
    {
        $sTable = $this->property['table'];
        $sIndexColumn = $this->property['colindex'];

        if(strpos($col,"|")!==false)
        {
            $aTmp = explode("|",$col);

            $aSubTable = $this->_getSubTableData($aTmp[0]);
            if($aSubTable===false)
            {
                die("NO SUBTABLE DEFINITION FOUND FOR ".$col);
            }

            $col = $aTmp[1];
            $sTable = $aSubTable['tablename'];
            $sIndexColumn = $aSubTable['foreigncolumn'];
        }


        if(trim($col)!="" && trim($this->property['colindex'])!="") {
            $hsconfig = getHsConfig();
            $sqlstring = "select `{$col}` from `{$sTable}` where `{$sIndexColumn}` = '{$hsconfig->escapeString($index)}'";
            if ($this->property['debugmode'] == "1") {
                echo $sqlstring . "<br>";
            }

            return $hsconfig->getScalar($sqlstring);
        }
        return "";
    }

    public function getEditorPropertyHeader()
    {
        $html='
        <div>
            <div><h1>Form</h1></div>
            <div>
                <h2>Standardvalues</h2>
                <table>
                    <tr>
                        <th align="left">Title</th>
                        <th align="left">Value</th>
                    <tr>
                    <tr>
                        <td>ID:</td>
                        <td>'.$this->getTabId().'</td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <button id="formmoveup" data-containerid="'.$this->getTabId().'" onclick="moveTabUp(\''.$this->getTabId().'\'); ">Move form up</button>
        <button id="formmovedown" data-containerid="'.$this->getTabId().'" onclick="moveTabDown(\''.$this->getTabId().'\'); ">Move form down</button>
        <hr>
        <div id="propertyformborder">
        <form id="propertyform" action="" method="POST" enctype="multipart/form-data">
            <div id="propertyformwait"></div>
        ';
        return $html;
    }
    public function getEditorProperty()
    {
        $html="";
        $html.=$this->getEditorPropertyHeader();
        $html.=$this->getEditorProperty_Textbox('Title (Parameter startformname)','bezeichnung');
        $html.=$this->getEditorProperty_Textbox("Customer ID (Parameter startform)",'customerid');
        $html.=$this->getEditorProperty_Textbox('Main table name:','table');
        $html.=$this->getEditorProperty_Textbox('Main index column (e. g. index1):','colindex');
        //$html.=$this->getEditorProperty_Textbox('Wenn das Formular immer mit den selben Autowert aufgerufen werden soll, hier die Bezeichnung f&uuml;r den Autowert eingeben: (Kann auch mit Parametern gesetzt werden, beim Aufruf des Formulares ($_REQUEST["FORMULARAUTOWERT"]), z. .b interpreter.php?XMLFILE=xxx.xml&FORMULARAUTOWERT=1234:','indexvalue');

        $html.=$this->getEditorProperty_Textarea('Sub table for 1 to 1 relations (one definition per line. Syntax: TABLENAME|PRIMARYKEY|FOREIGNKEY. If primarykey and foreign key is the same you have not to type in the foreign key)','subtabledefinition');

        $html.='<button id="formloaddefinition" type="button" onclick="loadDefinition(\''.$this->getTabId().'\'); return false; ">Load elements from the main table definition</button>';
        $html.=$this->getEditorProperty_Line();

        $html.=parent::getEditorProperty_Label("You can ask for a password, if the user make changes (Save action).<br>
        To do that, formedit call a script at the beginning before the data load. You can create a hash and save it into the session.<br>
        Before the formedit save the data it call a second script that return a message that should displayed. Under the message, the user
        can type in a password. That is send to the server to your script.<br>
        The script have to implement the 'validation' class in the folder 'scriptvalidation'.
        ");
        $html.=parent::getEditorProperty_Checkbox("Password validation",'passwordvalidation','0');
        $html.=parent::getEditorProperty_Textarea("Description",'passwordvalidation_desc');
        $html.=parent::getEditorProperty_Textbox("Classname",'passwordvalidation_class');
        
        
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorProperty_Line();
        $html.=$this->getEditorPropertyFooter(true);
        return $html;
    }
    public function getEditorPropertyFooter($submit=true)
    {
        $html='';
        if($submit)
        {
            $html.='<div style="padding-top:10px; padding-bottom:20px; ">
                <button id="propertysubmit" type="submit">Save</button>
            </div>';
        }
        $html.='<input type="hidden" name="containerid" value="'.$this->getTabId().'">
        </form></div>';
        return $html;
    }
	
	
	
	public function getData()
    {
        $element = array();
        $element['classname'] = get_class($this);

        // new feature, save line breaks as string arrays for better reading and git reporting
        $element['property'] = array_filter(array_map([$this, "getConvertToYaml"], $this->property->getRawArray()), function ($p) {
            return strpos($p, "_ROT13") === false;
        }, ARRAY_FILTER_USE_KEY);

        return $element;
    }

	public function setData($element)
    {
        $tmp=[];
        foreach ($element['property'] as $name => $value) {
            // new feature, save line breaks as string arrays for better reading and git reporting.
            $tmp[$name] = $this->setConvertFromYaml($value);
        }
        $this->property->setRawArray($tmp);
    }
}
