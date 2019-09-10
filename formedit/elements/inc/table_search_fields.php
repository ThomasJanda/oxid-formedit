<?php

// region Table Search Fields

abstract class tableSearchField
{
    public $sqlCommandOperator = "=";
    public $emelent;
    public $gridId = "";
    public $uniqueGridId = "";

    public function __construct($element, $gridId, $uniqueGridId)
    {
        $this->element = $element;
        $this->gridId = $gridId;
        $this->uniqueGridId = $uniqueGridId;
        //data-table-id="$this->gridId" data-table-unique-id="$this->uniqueGridId"
    }

    // region Get Help

    protected static $help = array(
        'title' => 'ERROR',
        'key' => '',
        'example' => "",
        'description' => "",
    );
    public static function getHelp() { return static::$help; }

    public static final function getAllHelp()
    {
        $tableSearchFieldTypes = array('textboxexact' => 1, 'textbox' => 1, 'datebox' => 1, 'dateboxmin' => 1,
            'dateboxmax' => 1, 'selectboxdb' => 1, 'selectboxdboperator' => 1, 'textboxoperator' => 1, 'custom' => 1,
            'checkbox' => 1, 'selectboxdbcustom' => 1, 'selectboxcustom' => 1, 'selectbox' => 1,
            'selectbox_list' => 1 );

        $help = array();
        foreach ($tableSearchFieldTypes as $class => $c) {
            $class = "tableSearchField_$class";
            $help[] = $class::getHelp();
        }

        $helps = implode("\n", array_map(function ($h) {
            $title = str_replace("'", "\\'", "$h[title]:\n $h[description]");
            return "<tr title='$title'><td>$h[key]</td><td style=font-family:monospace>$h[example]</td></tr>";
        }, $help));

        $html = "<table style=width:100%><tr><th style=text-align:left>Search Field</th><th style=text-align:left>Example</th></tr>\n$helps\n</table>";

        return $html;
    }

    // endregion

    // must have name=$inputName
    abstract function getSearchFieldHtml($inputName, $currentValue);

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        return strlen($escapedValue) == 0 ? ""
            : "$sqlname $this->sqlCommandOperator '$escapedValue'";
    }

    protected function getSearchSqlCommand_custom($escapedValue, $sqlname)
    {
        $parsedSqlname = str_replace('#VALUE#', $escapedValue, $sqlname);
        return strlen($escapedValue) == 0 ? ""
            : "($parsedSqlname)";
    }
}

class tableSearchField_textboxexact extends tableSearchField
{
    protected static $help = array(
        'title' => 'Text box, exact',
        'key' => 'textboxexact',
        'example' => "mytable.title|textboxexact|Title",
        'description' => "",
    );

    public function getSearchFieldHtml($inputName, $currentValue)
    {
        return <<<html
            <input style='border:1px solid #ddd;width:178px;box-sizing:border-box;padding:1px 4px' type=text name='$inputName' value='$currentValue'
                data-fe-where data-table-id="$this->gridId" data-table-unique-id="$this->uniqueGridId">
html;
    }
}

class tableSearchField_textbox extends tableSearchField_textboxexact
{
    protected static $help = array(
        'title' => 'Normal text box',
        'key' => 'textbox',
        'example' => "mytable.title|textbox|Title",
        'description' => "",
    );

    public function getSearchSqlCommand($escapedValue, $sqlName)
    {
        if (strlen($escapedValue) == 0) return "";

        // see if people are looking for the id instead of just the sku
        $skuFieldName = "coycomanufacturerid";
        $isSku = strpos($sqlName, $skuFieldName) !== false;
        $wantsId = strpos($escapedValue, "id:") === 0;
        if ($isSku && $wantsId) {
            $newSqlName = str_replace($skuFieldName, "index1", $sqlName);

            $newEscapedValue = explode(",", substr($escapedValue, 3));
            if (count($newEscapedValue) == 1) {
                return "$newSqlName = '$newEscapedValue[0]'";
            } else {
                $gluedEscapedValue = implode(", ", array_map(function ($id) { return "'$id'"; }, $newEscapedValue));
                return "$newSqlName in ($gluedEscapedValue)";
            }

        }

        // check if user wants exact match
        if (strpos($escapedValue, "=:") === 0) {
            $newEscapedValue = substr($escapedValue, 2);
            return "$sqlName = '$newEscapedValue'";
        }

        // check if user wants not match
        if (strpos($escapedValue, "!:") === 0) {
            $newEscapedValue = substr($escapedValue, 2);
            return "not $sqlName = '$newEscapedValue'";
        }

        // look for regexp searches, if first and last characters are ~ or / then it is a regex
        $pattern = '`^(?<negator>!)?(?<separator>[/~])(?<pattern>.*)\g<separator>(?<options>\w{0,4})$`';
        if (preg_match($pattern, $escapedValue, $match)) {
            // the search is a regex, use it instead of normal wildcard.
            $not = $match["negator"] ? "not" : "";
            return "$sqlName $not regexp '$match[pattern]'";
        }

        $wildCardValue = str_replace('*', '%', $escapedValue);
        return "$sqlName like '%$wildCardValue%'";
    }
}

class tableSearchField_datebox extends tableSearchField
{
    protected static $help = array(
        'title' => 'Date box',
        'key' => 'datebox',
        'example' => "mytable.orderdate|datebox|Orderdate",
        'description' => "",
    );

    public function getSearchFieldHtml($inputName, $currentValue)
    {
        return <<<html
            <input style='border:1px solid #dddddd;width:178px' type='text' name='$inputName' value='$currentValue'
                data-fe-where data-table-id="$this->gridId" data-table-unique-id="$this->uniqueGridId" data-fe-type="date">
html;
    }
}

class tableSearchField_dateboxmin extends tableSearchField_datebox
{
    protected static $help = array(
        'title' => 'Date box minimum',
        'key' => 'dateboxmin',
        'example' => "mytable.orderdate|dateboxmin|Orderdate min",
        'description' => "",
    );

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        return strlen($escapedValue) == 0 ? "" : "$sqlname >= '$escapedValue 00:00:00'";
    }
}

class tableSearchField_dateboxmax extends tableSearchField_datebox
{
    protected static $help = array(
        'title' => 'Date box maximum',
        'key' => 'dateboxmax',
        'example' => "mytable.orderdate|dateboxmax|Orderdate max",
        'description' => "",
    );

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        return strlen($escapedValue) == 0 ? "" : "$sqlname <= '$escapedValue 23:59:59'";
    }
}

class tableSearchField_selectboxdb extends tableSearchField
{
    protected static $help = array(
        'title' => 'Dropdown database',
        'key' => 'selectboxdb',
        'example' => "mytable.title|selectboxdb::select distinct(city) from oxuser order by city|City",
        'description' => "",
    );

    public $sqlCommandOperator = "like";

    public function getOptions($currentValue)
    {
        $hsconfig = getHsConfig();
        $db = $hsconfig->getDbId();

        list(, $tmpsql) = array_map('trim', explode("::", "$this->element::")); // adding extra colons to avoid php errors
        $tmpsql = $hsconfig->parseSQLString($tmpsql);

        $rsOptions = array();
        if ($rs = $db->query($tmpsql)) {
            while ($row = $rs->fetch_row()) {
                if(is_array($currentValue))
                    $isSelected = in_array($row[0],$currentValue) ? "selected" : "";
                else
                    $isSelected = $row[0] == $currentValue ? "selected" : "";
                $rsOptions[] = array('value' => $row[0], 'text' => $row[1], 'isSelected' => $isSelected);
            }
            $rs->close();
        }

        return $rsOptions;
    }

    public function getOptionsNoSql($currentValue)
    {
        list(, $options) = array_map('trim', explode("::", "$this->element::")); // adding extra colons to avoid php errors

        // Applied default values.
        if (!$options) $options = "1 => Y, 0 => N";

        // this helps simplify the checks for true|false in fields, will generate this: ( and a.active | and not a.active )
        if ($options == "tinyint") {
            $options = "#EMPTY# => Y, not => N";
            // this can happen with standard values that are 0 and 1
            if ($currentValue == "0") $currentValue = "not";
            if ($currentValue == "1") $currentValue = "#EMPTY#";
        }

        $renderedOptions = array_map(function ($kv) use($currentValue) {
            list ($k, $v) = array_map('trim', explode('=>', "$kv=>")); // adding => to avoid php error
            if(is_array($currentValue))
                $isSelected = in_array($k,$currentValue) ? "selected" : "";
            else
                $isSelected = $k == $currentValue ? "selected" : "";
            return array('value' => $k, 'text' => $v, 'isSelected' => $isSelected);
        }, array_map('trim', explode(',', $options)));

        return $renderedOptions;
    }

    public function getSearchFieldHtml($inputName, $currentValue)
    {
        $options = $this->getOptions($currentValue);

        $htmlOptions = implode("\n", array_map(function ($o) {
            return "<option value='$o[value]' $o[isSelected]>$o[text]</option>";
        }, $options));

        return <<<html
            <select style='border:1px solid #dddddd;width:178px' id='$inputName' name='$inputName'
                data-fe-where data-table-id="$this->gridId" data-table-unique-id="$this->uniqueGridId">
                <option value=''>Please choose</option>
                $htmlOptions
            </select>
html;
    }
}

class tableSearchField_selectboxdboperator extends tableSearchField_selectboxdb
{
    protected static $help = array(
        'title' => 'Dropdown database w/operator',
        'key' => 'selectboxdboperator',
        'example' => "mytable.title|selectboxdboperator::select distinct(city) from oxuser order by city::OPERATOR (=,like,<,>...)|City",
        'description' => "",
    );

    public function __construct($element, $gridId, $uniqueGridId)
    {
        parent::__construct($element, $gridId, $uniqueGridId);

        // select box db operator has this format: selectboxdboperator::#SQL#::#OPERATOR#
        list($sbdbo, $tmpsql, $op) = array_map('trim', explode("::", "$this->element:: ::"));

        if ($op) $this->sqlCommandOperator = $op;
    }
}

class tableSearchField_textboxoperator extends tableSearchField_textboxexact
{
    protected static $help = array(
        'title' => 'Normal text box w/operator',
        'key' => 'textboxoperator',
        'example' => "mytable.title|textboxoperator::operator::prefix::suffix|Title",
        'description' => "operator can be like|=|not etc. For prefix/suffix you can use wildcards (%)",
    );

    public $prefix = "";
    public $suffix = "";

    public function __construct($element, $gridId, $uniqueGridId)
    {
        parent::__construct($element, $gridId, $uniqueGridId);

        // text box db operator has this format: textboxoperator::#OPERATOR#::#PREFIX#::#SUFFIX#
        list($tbdbo, $op, $prefix, $suffix) = array_map('trim', explode("::", "$this->element:: :: ::"));

        if ($op) $this->sqlCommandOperator = strtolower($op);
        if ($prefix) $this->prefix = $prefix;
        if ($suffix) $this->suffix = $suffix;
    }

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        if ($this->sqlCommandOperator == "like") $escapedValue = str_replace("*", "%", $escapedValue);

        return strlen($escapedValue) == 0 ? ""
            : "$sqlname $this->sqlCommandOperator '{$this->prefix}{$escapedValue}{$this->suffix}'";
    }
}

class tableSearchField_custom extends tableSearchField_textboxexact
{
    protected static $help = array(
        'title' => 'Custom Text',
        'key' => 'custom',
        'example' => "index1 = (select findex1 from table where searchcolumn='#VALUE#')|custom|displayname",
        'description' => "User can type in a value in a textbox that replace with the variable #VALUE# in the sql statment",
    );

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        return $this->getSearchSqlCommand_custom($escapedValue, $sqlname);
    }
}

class tableSearchField_checkbox extends tableSearchField
{
    protected static $help = array(
        'title' => 'Checkbox',
        'key' => 'checkbox',
        'example' => "mytable.active = #VALUE#|checkbox::1|Active?",
        'description' => "Give any valid where expression and the token #VALUE# will be replaced by the value from the user",
    );

    function getSearchFieldHtml($inputName, $currentValue)
    {
        list($cb, $val) = array_map('trim', explode("::", "$this->element::")); // adding extra colons to avoid php errors

        $checked = $currentValue == $val ? "checked" : "";
        return <<<html
            <input type='hidden' name='$inputName' value=''><!-- send empty value instead of not set, to know if use from session or use emtpy -->
            <input type='checkbox' $checked name='$inputName' value='$val'
                data-fe-where data-table-id="$this->gridId" data-table-unique-id="$this->uniqueGridId">
html;
    }

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        return $this->getSearchSqlCommand_custom($escapedValue, $sqlname);
    }
}

class tableSearchField_selectboxdbcustom extends tableSearchField_selectboxdb
{
    protected static $help = array(
        'title' => 'Dropdown database custom',
        'key' => 'selectboxdbcustom',
        'example' => "index1 = '#VALUE#'|selectboxdbcustom::select distinct city from oxuser|City",
        'description' => "",
    );

    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        return $this->getSearchSqlCommand_custom($escapedValue, $sqlname);
    }
}

class tableSearchField_selectboxcustom extends tableSearchField_selectboxdbcustom
{
    protected static $help = array(
        'title' => 'Dropdown database custom',
        'key' => 'selectboxcustom',
        'example' => "t.myfield = '#VALUE#'|selectboxcustom::1=>Y,0=>N|My Field",
        'description' => "Send custom key/value pairs. The default is the normal Y/N",
    );

    public function getOptions($currentValue)
    {
        return $this->getOptionsNoSql($currentValue);
    }
}

class tableSearchField_selectbox extends tableSearchField_selectboxdb
{
    protected static $help = array(
        'title' => 'Dropdown no DB',
        'key' => 'selectbox',
        'example' => "t.active|selectbox::1=>Y,0=>N|Active",
        'description' => "Send custom key/value pairs. The default is the normal Y/N",
    );

    public $sqlCommandOperator = "=";

    public function getOptions($currentValue)
    {
        list(, $options) = array_map('trim', explode("::", "$this->element::")); // adding extra colons to avoid php errors

        // it can be a select string
        if (strpos(strtolower($options), "select") === 0) {
            return parent::getOptions($currentValue);
        }

        // it can also be a pre-calculated set of options.
        return $this->getOptionsNoSql($currentValue);
    }

    // override
    public function getSearchSqlCommand($escapedValue, $sqlname)
    {
        if(!is_array($escapedValue) && strlen($escapedValue) == 0) {
            return "";
        }

        list(, $options) = array_map('trim', explode("::", "$this->element::")); // adding extra colons to avoid php errors

        if ($options == "tinyint") {
            if ($escapedValue == "1") $escapedValue = "#EMPTY#";
            if ($escapedValue == "0") $escapedValue = "not";
        }

        // simple yes-no field and it is faster to render it with a NOT type of condition, like this: (and not a.active)
        return $options == "tinyint"
                ? "$escapedValue $sqlname"
                : "$sqlname $this->sqlCommandOperator ('".(is_array($escapedValue)?implode("','",$escapedValue):$escapedValue)."')";
    }

    public function getSearchFieldHtml($inputName, $currentValue)
    {
        return parent::getSearchFieldHtml($inputName, $currentValue);
    }
}



















class tableSearchField_selectbox_list extends tableSearchField_selectbox
{
    protected static $help = array(
        'title' => 'Dropdown no DB',
        'key' => 'selectbox_list',
        'example' => "t.colors|selectbox_list::green=>Green,red=>Red,yellow=>Yellow|Colors",
        'description' => "Multi select box where the user can select different values at the same time. The selected values will combine with 'or'",
    );

    public $sqlCommandOperator = "in";

    public function getSearchFieldHtml($inputName, $currentValue)
    {
        $options = $this->getOptions($currentValue);

        $htmlOptions = implode("\n", array_map(function ($o) {
            return "<option value='$o[value]' $o[isSelected]>$o[text]</option>";
        }, $options));

        return "<select multiple size='3' style='border:1px solid #dddddd; width:178px' id='".$inputName."' name='".$inputName."[]'
                data-fe-where data-table-id='".$this->gridId."' data-table-unique-id='".$this->uniqueGridId."'>
                ".$htmlOptions."
            </select>";
    }
}

// endregion
