<?php

class basecontrol extends baseelement
{ // implements Serializable

    protected $request;

    public $name = "basecontrol";

    public $editorname = "Basecontrol";
    public $editorcategorie = "Formelement";
    public $editorshow = false;

    public $isparentcontrol = false;

    public $id = "";
    public $left = 0;
    public $top = 0;
    public $height = 20;
    public $width = 200;
    public $zindex = 10000;
    public $containerid = "";

    public $ainterpretererrorlist = [];

    public function __construct()
    {
        parent::__construct();
        $this->createid();
    }

    public function __sleep()
    {//Serialize everything except the request.
        return array_diff(array_keys(get_object_vars($this)), ['request']);
    }

    public function __wakeup()
    {//On unserialize, recapture the request.

    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Concat the class, id and the field we need to get from
     *
     * @param $field
     *
     * @return string
     */
    protected function getFieldName($field)
    {
        return get_called_class().$this->id.$field;
    }

    /**
     * Get a field from the request
     *
     * @param string $field
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function getRequestField($field, $default = null)
    {
        $field = $this->getFieldName($field);

        $sRet = $default;
        if(isset($_REQUEST[$field]))
            $sRet = $_REQUEST[$field];

        return $sRet;
    }

    /**
     * @var string[]
     */
    //public $property = array();

    /**
     * @var basetab $otab
     */
    public $otab = null;

    /**
     * @param basetab $otab
     */
    public function setTab($otab)
    {
        $this->otab = $otab;
    }

    /**
     * @return basetab
     */
    public function getTab()
    {
        return $this->otab;
    }

    /**
     * @return string
     */
    public function getDatabaseColumn()
    {
        return $this->property['datenbankspalte'];
    }


    public function generateLanguageId()
    {
        $class = get_class($this);
        $tab = $this->otab->getTabName();
        $table = $this->otab->getTableName();
        $dbfield = $this->property['datenbankspalte'];
        $cid = $this->getCustomerId();

        if ($tab != "") {
            $tab .= "-";
        }
        if ($class != "") {
            $class .= "-";
        }
        if ($table != "") {
            $table .= "-";
        }
        if ($dbfield != "") {
            $dbfield .= "-";
        }
        if ($cid != "") {
            $cid .= "-";
        }

        $lid = $tab.$class.$table.$dbfield.$cid.uniqid("");
        $this->property['languageid'] = $lid;
    }

    public function createid()
    {
        $this->id = "element".uniqid("");
    }

    public function getCol()
    {
        $hsconfig = getHsConfig();
        $dbfield = $this->property['datenbankspalte'];
        $dbfield = str_replace('#EDITLANG#', $hsconfig->getLangColSuffix(), $dbfield);

        // depending on the start parameters this value can change
        $dbfield = $hsconfig->parseSQLString($dbfield);
        $dbfield = $this->replaceExtraSql($dbfield);

        return $dbfield;
    }

    public function getParentControl()
    {
        return $this->property['hasparentcontrol'];
    }

    public function setParentControl($value)
    {
        $this->property['hasparentcontrol'] = $value;
    }

    public function getParentControlCss()
    {
        $css = [];
        if ($parent = $this->getParentControl()) {
            list($tabContainer, $tabId, $tabIndex) = explode("_", $parent."__");

            $tabsCookie = json_decode($_COOKIE["fe-tabs"] ?? "{}");

            $selfCookieKey = "$tabContainer-$tabId";
            $id = $tabsCookie->$selfCookieKey ?? 0;

            if ($tabIndex != $id) {
                $css["display"] = "none";
            }

            if ($_REQUEST['visible']) {
                $css["display"] = "block";
            }
        }

        return $this->buildCssString($css);
    }

    public function setInterpreterErrorlist($errorlist)
    {
        $this->ainterpretererrorlist = $errorlist;
    }

    public function interpreterInit()
    {
        //override standardvalue if call with the correct parameter
        //feature formedit can fill from outside
        $dbcolumn = (isset($this->property['datenbankspalte']) ? $this->property['datenbankspalte'] : '');
        if ($dbcolumn != "" && isset($_REQUEST["dbcolumn__".$dbcolumn])) {
            $v = trim(stripslashes($_REQUEST["dbcolumn__".$dbcolumn]));
            if (isset($this->property['standardtext'])) {
                $this->property['standardtext'] = $v;
            }
        }


    }

    public function interpreterLoadLang()
    {
        if ($lid = $this->getLanguageId()) {
            $hsconfig = getHsConfig();
            $prop = $hsconfig->getLang($lid);

            if (is_array($prop)) {
                foreach ($prop as $key => $value) {
                    $this->property[$key] = $value;
                }
            }
        }
    }

    //public function interpreterSetElements(&$aoelements)
    //{
    //    $this->aoelements = $aoelements;
    //}

    /**
     * @return basecontrol[]|null
     */
    public function interpreterGetElements()
    {
        $hsconfig = getHsConfig();

        return $hsconfig->getElements();
    }

    public function interpreterFinish()
    {
        return "";
    }

    public static function interpreterFinish_static()
    {
        return "";
    }

    /**
     * at the end of page, javascript can write into this place
     *
     * @return string
     */
    public function interpreterFinishJavascript()
    {
        return "";
    }

    /**
     * at the end of page, javascript can write into this place
     * static version. just once per project, not once per element instance.
     *
     * @return string
     */
    public static function interpreterFinishJavascript_static()
    {
        return "";
    }

    public function interpreterProveNew($table, $colindex, $indexvalue)
    {
        return $this->interpreterProve($table, $colindex, $indexvalue);
    }

    public function interpreterProveEdit($table, $colindex, $indexvalue)
    {
        return $this->interpreterProve($table, $colindex, $indexvalue);
    }

    public function interpreterProve($table, $colindex, $indexvalue)
    {
        $error = "";
        if (isset($this->property['pflichtfeld']) && isset($this->property['fehlermeldung'])) {
            if ($this->property['pflichtfeld'] == '1') {
                if ($this->getInterpreterRequestValue() == "") {
                    $error = $this->property['fehlermeldung'];
                }
            }
            if ($error != "") {
                return [$this->id => $error];
            }
        }

        return false;
    }

    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        return $this->commonSaveAction($table);
    }

    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        return $this->commonSaveAction($table);
    }

    public function commonSaveAction($table)
    {
        $s = false;
        if (isset($this->property['datenbankspalte'])) {
            if ($col = $this->getCol()) {

                if (strpos($col, "|") !== false) {
                    $aTmp = explode("|", $col);
                    $table = $aTmp[0];
                    $col = $aTmp[1];
                }

                $s['table'] = $table;
                $s['foreignkey'] = ($this->property['foreignkey'] ?? "0" === "1" ? true : false);
                $s['col'] = $col;
                $s['value'] = $this->getInterpreterRequestValueForDb();
                $s['element'] = $this;
                //}
            }
        }

        return $s;
    }

    public function interpreterDeleteKennzeichen1($table, $colindex, $indexvalue)
    {
    }

    public function interpreterBeforeDeleteKennzeichen1()
    {
    }

    public function interpreterAfterDeleteKennzeichen1()
    {
    }

    public function interpreterDelete($table, $colindex, $indexvalue)
    {
    }

    public function interpreterBeforeDelete()
    {
    }

    public function interpreterAfterDelete()
    {
    }

    public function interpreterBulkDelete($table, $colindex, $indexvalue)
    {
    }

    public function interpreterBeforeBulkDelete()
    {
    }

    public function interpreterAfterBulkDelete()
    {
    }

    public function interpreterBeforeProveNew()
    {
    }

    public function interpreterAfterProveNew()
    {
    }

    public function interpreterBeforeSaveNew()
    {
    }

    public function interpreterAfterSaveNew()
    {
    }

    public function interpreterFinishedSaveNew()
    {

    }

    public function interpreterBeforeProveEdit()
    {
    }

    public function interpreterAfterProveEdit()
    {
    }

    public function interpreterBeforeSaveEdit()
    {
    }

    public function interpreterAfterSaveEdit()
    {
    }

    public function interpreterFinishedSaveEdit()
    {

    }

    public function interpreterBeforeRender()
    {
    }

    public function interpreterAfterRender()
    {
    }

    protected $isfirstnew = false;
    protected $isfirstedit = false;
    protected $isnew = false;
    protected $isedit = false;

    public function setInterpreterIsFirstNew()
    {
        $this->isfirstnew = true;
    }

    public function getInterpreterIsFirstNew()
    {
        return $this->isfirstnew;
    }

    public function getInterpreterIsNew()
    {
        return $this->isnew;
    }

    public function setInterpreterIsNew()
    {
        $this->isnew = true;
    }

    public function setInterpreterIsFirstEdit()
    {
        $this->isfirstedit = true;
    }

    public function getInterpreterIsFirstEdit()
    {
        return $this->isfirstedit;
    }

    public function setInterpreterIsEdit()
    {
        $this->isedit = true;
    }

    public function getInterpreterIsEdit()
    {
        return $this->isedit;
    }

    public function isInterpreterFirstLoad()
    {
        if ($this->isfirstnew == true || $this->isfirstedit == true) {
            return true;
        }

        return false;
    }

    public function getInterpreterRequestValue()
    {
        //feature formedit can fill from outside
        $dbcolumn = isset($this->property['datenbankspalte']) ? $this->property['datenbankspalte'] : '';
        if ($dbcolumn != "" && isset($_REQUEST["dbcolumn__".$dbcolumn])) {
            return trim(stripslashes($_REQUEST["dbcolumn__".$dbcolumn]));
        }

        $dbcolumn = isset($this->property['customerid']) ? $this->property['customerid'] : '';
        if ($dbcolumn != "" && isset($_REQUEST["customeridcolumn__".$dbcolumn])) {
            return trim(stripslashes($_REQUEST["customeridcolumn__".$dbcolumn]));
        }

        if (isset($_REQUEST[$this->id]) == false || is_array($_REQUEST[$this->id])) {
            return "";
        }

        return trim(stripslashes($_REQUEST[$this->id]));
    }

    /**
     * @return string
     */
    public function getInterpreterRequestValueForDb()
    {
        $sValue = $this->getInterpreterRequestValue();
        if ($this->property['allownull'] && trim($sValue) == "") {
            $sValue = "'#NULL#'";
        } else {
            $hsconfig = getHsConfig();
            $sValue = "'".$hsconfig->escapeString($sValue)."'";
        }

        return $sValue;
    }

    /**
     * Overrides a value on the request. Commonly used to capitalize strings or pre-format data.
     *
     * @param $value
     */
    public function setInterpreterRequestValue($value)
    {
        //echo __FUNCTION__."1<br>";
        $field = "";
        $value = trim(stripslashes($value));

        if (isset($_REQUEST[$this->id]) && ! is_array($_REQUEST[$this->id])) {
            $field = $this->id;
        }
        if ($field !== "") {
            $_REQUEST[$field] = $value;
        }

        //echo __FUNCTION__."2<br>";
        $field = "";
        $dbcolumn = isset($this->property['datenbankspalte']) ? $this->property['datenbankspalte'] : '';
        if ($dbcolumn != "" && isset($_REQUEST["dbcolumn__".$dbcolumn])) {
            $field = "dbcolumn__".$dbcolumn;
        }
        if ($field !== "") {
            $_REQUEST[$field] = $value;
        }

        //echo __FUNCTION__."3<br>";
        $field = "";
        $dbcolumn = $this->getCustomerId();
        if ($dbcolumn != "") {
            $field = "customeridcolumn__".$dbcolumn;
        }
        if ($field !== "") {
            $_REQUEST[$field] = $value;
        }

    }

    public function getInterpreterRequestValues()
    {
        if (isset($_REQUEST[$this->id]) == false) {
            return [];
        }

        $a = [];
        if (is_array($_REQUEST[$this->id])) {
            foreach ($_REQUEST[$this->id] as $key => $value) {
                $a[$key] = trim(stripslashes($value));
            }
        }

        return $a;
    }

    public function getInterpreterRender()
    {
        $text = "";
        $e = '<div data-hasparentcontrol="'.$this->getParentControl().'" class="element" id="'.$this->id.'" style="'
            .$this->getParentControlCss().'left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
            <input type="hidden" name="classname" value="'.get_class($this).'">
            <input type="hidden" name="containerid" value="'.$this->containerid.'">
            &nbsp;'.($text != "" ? $text." (" : "").$this->editorcategorie.' - '.$this->editorname.($text != "" ? ")" : "").'
        </div>';

        return $e;
    }

    public function editorBeforeRender()
    {
        return "";
    }

    protected $_sEditorElementCss = "";

    public function getEditorRender($text = "")
    {
        /*resize:both; overflow:scroll; overflow-y: hidden; overflow-x: hidden;*/
        $e = '<div data-hasparentcontrol="'.$this->getParentControl().'" 
            class="element" 
            id="'.$this->id.'" 
            style="
                '.$this->getParentControlCss().'
                left:'.$this->left.'px; 
                top:'.$this->top.'px; 
                width:'.$this->width.'px; 
                height:'.$this->height.'px; 
                z-index:1; 
                '.$this->_sEditorElementCss.'
            ">
            <input type="hidden" name="classname" value="'.get_class($this).'">
            <input type="hidden" name="containerid" value="'.$this->containerid.'">
            &nbsp;'.($text != "" ? $text." (" : "").$this->editorcategorie.' - '.$this->editorname.($text != "" ? ")" : "").'
        </div>';

        return $e;
    }


    public function getEditorPropertyHeader()
    {
        $html = '
        <div>
            <div><h1>'.$this->editorcategorie.' - '.$this->editorname.'</h1></div>
            <div>'.$this->editordescription.'</div>
            <div>
                <h2>Standardvalues</h2>
                <table>
                    <tr>
                        <th align="left">Title</th>
                        <th align="left">Value</th>
                    <tr>
                    <tr>
                        <td>ID:</td>
                        <td>'.$this->id.'</td>
                    </tr>
                    <tr>
                        <td>z-Index:</td>
                        <td>'.$this->zindex.'</td>
                    </tr>
                    <tr>
                        <td>Left:</td>
                        <td>'.$this->left.' px</td>
                    </tr>
                    <tr>
                        <td>Top:</td>
                        <td>'.$this->top.' px</td>
                    </tr>
                    <tr>
                        <td>Width:</td>
                        <td>'.$this->width.' px</td>
                    </tr>
                    <tr>
                        <td>Height:</td>
                        <td>'.$this->height.' px</td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        <div id="propertyformborder">
        <form id="propertyform" action="" method="POST" enctype="multipart/form-data">
            <div id="propertyformwait"></div>
        ';

        return $html;
    }

    public function getEditorProperty()
    {
        $html = $this->getEditorPropertyHeader().$this->getEditorPropertyFooter(false, false);

        return $html;
    }

    /**
     * @param bool $submit
     * @param bool $dbelement
     * @param bool $taborder
     * @param bool $showtabs
     * @param bool $showlanguagevariable
     * @param bool $showinvisible
     * @param bool $showstyle
     * @param bool $groupTerminator
     * @param bool $allowNull
     * @param bool $isForeignKey
     *
     * @return string
     */
    public function getEditorPropertyFooter(
        $submit = true,
        $dbelement = true,
        $taborder = true,
        $showtabs = true,
        $showlanguagevariable = true,
        $showinvisible = true,
        $showstyle = true,
        $groupTerminator = false,
        $allowNull = false,
        $isForeignKey = false
    ) {
        $html = '';
        if ($dbelement) {
            $html .= $this->getEditorProperty_Textbox("Database Column (Variables: #EDITLANG#. If you use a sub table use SUBTABLENAME|COLUMNNAME)",
                'datenbankspalte');
            $html .= $this->getEditorProperty_Textarea("Description from the Database Column", 'datenbankspaltebeschreibung');

            if ($allowNull) {
                $html .= $this->getEditorProperty_Checkbox("Allow null? (save null instead of empty string)", 'allownull');
            }
            if ($isForeignKey) {
                $html .= $this->getEditorProperty_Checkbox("Is a foreign key? (when a new record will send to the database, this value has to add to the 'insert' statement to not harm the constraint of the foreign key definition because the column doesn´t allow null)",
                    'foreignkey');
            }
        }

        /*
        if($csselement)
            $html.=$this->getEditorProperty_Textbox("CSS",'css');
        */

        if ($taborder) {
            $html .= $this->getEditorProperty_Textbox("Tab-Order", 'taborder');
        }

        if ($showtabs) {
            $html .= $this->getEditorProperty_SelectboxTabs("Parent Tab", 'hasparentcontrol');
        }

        $html .= $this->getEditorProperty_Textbox("Customer ID", 'customerid');

        if ($showlanguagevariable) {
            $html .= $this->getEditorProperty_Textbox("Language ID", 'languageid');
        }

        if ($showinvisible) {
            $html .= $this->getEditorProperty_Checkbox("Invisible", 'invisible');
        }

        if ($showstyle) {
            $html .= $this->getEditorProperty_Textbox("CSS Style", 'style');
        }

        if ($groupTerminator) {
            $html .= $this->getEditorProperty_Line(null, true, false);
        }

        if ($submit) {
            $html .= '<div style="padding-top:10px; padding-bottom:20px; ">
                <button id="propertysubmit" type="submit">Save</button>
            </div>';
        }
        $html .= '<input type="hidden" name="containerid" value="'.$this->containerid.'">
        <input type="hidden" name="id" value="'.$this->id.'">
        <input type="hidden" name="classname" value="'.get_class($this).'">
        </form></div>';

        return $html;
    }

    function getSQL($table)
    {
        $hsConfig = getHsConfig();
        $dbfield = $this->property['datenbankspalte'];
        $dbfield = str_replace('#EDITLANG#', '', $dbfield);

        if (trim($dbfield) == "" || $table == "") {
            return "";
        }
        $sqlstring = "alter table `".$table."` add column `".$dbfield."` VARCHAR(250) DEFAULT '' ";

        $dbfielddescription = trim($this->property['datenbankspaltebeschreibung']);
        if ($dbfielddescription != "") {
            $sqlstring .= " COMMENT '".$hsConfig->escapeString($dbfielddescription)."'";
        }
        $sqlstring .= ";";

        return $sqlstring;
    }

    function getLanguageArray($add = [])
    {
        $lang = [];
        $lid = $this->getLanguageId();
        if ($lid != "") {
            foreach ($this->property as $name => $value) {
                if ($name == "bezeichnung" || $name == "title" || ($name == "fehlermeldung" && $this->property['pflichtfeld'] == "1")
                    || $name == "fehlermeldungpflichtfeld"
                    || $name == "helptext"
                    || $name == "standardtext"
                ) {
                    if ($value != "") {
                        $lang[$lid][$name] = $value;
                    }
                }
            }


            foreach ($add as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $n => $v) {
                        $lang[$lid][$name][$n] = $v;
                    }
                } else {
                    $lang[$lid][$name] = $value;
                }
            }

        }

        /*
        echo '<pre>';
        print_r($lang);
        echo '</pre>';
        */

        return $lang;
    }


    public function getData()
    {
        $element = [];
        $element['classname'] = get_class($this);

        // new feature, save line breaks as string arrays for better reading and git reporting
        $element['property'] = array_filter(array_map([$this, "getConvertToYaml"], $this->property->getRawArray()),
            function ($p) {
                return strpos($p, "_ROT13") === false;
            }, ARRAY_FILTER_USE_KEY);

        $element['id'] = $this->id;
        $element['left'] = $this->left;
        $element['top'] = $this->top;
        $element['height'] = $this->height;
        $element['width'] = $this->width;
        $element['zindex'] = $this->zindex;
        $element['containerid'] = $this->containerid;

        return $element;
    }

    public function setData($element)
    {
        $tmp = [];
        foreach ($element['property'] as $name => $value) {
            // new feature, save line breaks as string arrays for better reading and git reporting.
            $tmp[$name] = $this->setConvertFromYaml($value);
        }
        $this->property->setRawArray($tmp);

        $this->id = $element['id'];
        $this->left = $element['left'];
        $this->top = $element['top'];
        $this->height = $element['height'];
        $this->width = $element['width'];
        $this->zindex = $element['zindex'];
        $this->containerid = $element['containerid'];
    }

    /**
     * This function makes formedit projects more versatile, depending on a start parameter, you can choose a different
     * sql statement that returns the results. As an example you can look at the file cmPm_main.cpf in the module
     * cm/productMgmt. Santi.
     *
     * @param $sql
     *
     * @return mixed
     */
    public static function replaceExtraSql($sql)
    {
        $regex = '~
            /\*EXTRA             # literal: /*EXTRA
            (?:                  # non-capturing (used for grouping and applying "?")
              \(                 # literal: (
              (                  # capture group 1
                [^)]*            # one or many non ")"
              )
              \)                 # literal: )
            )?                   # group can either be once or zero times.
            (.*?)                # capture group 2, this is the bulk of the content. lazy
            EXTRA\*/             # literal: EXTRA*/
        ~sx';

        $sql = preg_replace_callback($regex, function ($matches) {

            /*EXTRA(#STARTPARAM.TYPE#)
            type1:>join cpv_cpCar_articleMask_rendered am on a.index1 = am.index1<:
            type2,type3:>join another table or leave blank.<:
            else,always:>bla bla<:
            EXTRA*/

            $key = $matches[1];
            // get groups, remove empty entries and trim trailing stuff.
            $rawOptions = array_filter(array_map('trim', explode('<:', $matches[2])));

            $options = [];
            $regexOptions = [];
            foreach ($rawOptions as $raw) {
                // get the key and value parts of the raw option, key can be multiple keys separated by commas.
                list($ks, $v) = array_map('trim', explode(':>', $raw));
                $kk = array_map('trim',
                    explode(',', $ks)); // sometimes two keys can share the same option. separate them by comma

                foreach ($kk as $k) {
                    $k = trim($k);
                    // if string starts and ends with a regex character then treat as regex.
                    if (preg_match('#^(!)?(([/~`]).*\3)$#', $k, $match)) {
                        $regexOptions[$match[2]]["negated"] = $match[1] == "!";
                        $regexOptions[$match[2]]["values"][] = trim($v);
                    } else {
                        // aggregate options in a single place of the array, each key can appear multiple times and here it is
                        // put together.
                        $options[$k][] = trim($v);
                    }
                }
            }
            // even if no match is found, replace the entire thing at least by an empty string.
            $replacement = '';
            // is there a match between the received option and the options we found?
            $doByKey = isset($options[$key]);
            // regex is checked afterwards.
            $doByRegexKey = false;
            // if there is no match, but there is an "else" key, use it
            $doByElse = isset($options["else"]);
            // if there was a match and there is an "also" key, apply it at the end.
            $doByAlso = ($doByKey || $doByElse) && isset($options["also"]);
            // if there was a match and there is an "but first" key, apply it at the beginning.
            $doByButFirst = ($doByKey || $doByElse) && isset($options["butfirst"]);

            // each match can be divided by a separator, normally a whitespace, but it can be another string.
            $separator = isset($options["separator"]) ? $options["separator"][0] : " ";
            if ($doByKey) {
                // if we find the passed option in our found options.
                $replacement .= implode($separator, $options[$key]);
            }
            foreach ($regexOptions as $pattern => $values) {
                $isMatch = (bool)preg_match($pattern, $key);
                if ($isMatch == ! $values["negated"]) {
                    $doByRegexKey = true;
                    if ($replacement) {
                        $replacement = $separator.$replacement;
                    }
                    $replacement .= implode($separator, $values["values"]);
                }
            }
            if ( ! $doByKey && ! $doByRegexKey && $doByElse) {
                // if there is an else statement.
                $replacement = implode($separator, $options["else"]);
            }
            // always there is a match, do this too
            if ($doByAlso) {
                // if there is an always statement.
                $also = implode($separator, $options["also"]);
                $replacement = "$replacement $also";
            }
            // similar to also, but first.
            if ($doByButFirst) {
                // if there is an always statement.
                $butFirst = implode($separator, $options["butfirst"]);
                $replacement = "$butFirst $replacement";
            }

            return $replacement;
        }, $sql);

        return $sql;
    }

    protected function buildCssString(array $css)
    {
        $css = array_map(function ($cssName, $cssValue) {

            // if value is false-like then don't print attribute.
            if ( ! $cssValue) {
                return null;
            }

            return "$cssName:$cssValue;";

        }, array_keys($css), $css);

        // filtering out null or false attributes.
        $css = array_values(array_filter($css));

        $css = implode("", $css);

        return $css;
    }

    protected function buildHtmlAttributes(array $attributes)
    {
        // convert attribute list into attributes string for element.
        $attributes = array_map(function ($attrName, $attrValue) {
            // if value is null then don't print attribute.
            if ($attrValue === null || $attrValue === false) {
                return null;
            }

            // if value is empty string then print attribute without equals sign
            if ($attrValue == "" || $attrValue === true) {
                return $attrName;
            }

            // value can be an array if necessary, but it needs escaping.
            // note. jquery $.data() auto-parses json if found.
            if (is_array($attrValue)) {
                $attrValue = json_encode($attrValue, JSON_UNESCAPED_SLASHES);
            }

            // auto add " to string at beginning and end and escape any " in the middle of it.
            $attrValue = json_encode($attrValue, JSON_UNESCAPED_SLASHES);

            // normal case, just return attribute equals value.
            return "$attrName=$attrValue";

        }, array_keys($attributes), $attributes);

        // filtering out null or false attributes.
        $attributes = array_values(array_filter($attributes));

        // implode into single attribute chain separated by spaces.
        $attributes = implode(" ", $attributes);

        return $attributes;
    }

    public function getDebugInfo()
    {
        $debugInfo = parent::getDebugInfo();
        $ci = $this->getCustomerId();

        return [
                "id"        => $this->id,
                "name"      => $this->editorname.($ci ? " ($ci)" : ""),
                "debugInfo" => $this->debugInfo,
            ] + $debugInfo;
    }
}
