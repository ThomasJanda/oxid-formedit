<?php

/**
 * Class baseelement
 *
 * @property hsproperty property
 */
abstract class baseelement
{
    protected $_aProperty = null;

    public function __get($name)
    {
        if ($name == "property") {
            return $this->_aProperty;
        }

        return null;
    }

    public function __construct()
    {
        $this->_aProperty = new hsproperty();
    }

    public function getProperty($key)
    {
        return $this->_aProperty[$key];
    }

    public function setProperty($key, $value)
    {
        $this->_aProperty[$key] = $value;
    }


    // region Abstract functions

    // These functions must be implemented from the extend classes.

    /**
     * Takes information decoded from the save-file, it is an array of the form:
     *      array('classname' => '...', 'property' => array(...))
     *
     * @param $element
     *
     * @return mixed
     */
    abstract function setData($element);


    abstract function getData();

    // endregion

    protected function getConvertToYaml($value)
    {
        // some browsers post \r\n and we don't want that, and it also breaks nice yaml syntax.
        return preg_replace('~ *\R~', "\n", $value);
    }

    protected function setConvertFromYaml($value)
    {
        // this is legacy code, we should never split into arrays anymore.
        return is_array($value)
            ? implode("\n", $value)
            : $value;
    }

    public function getCustomerId()
    {
        return $this->property['customerid'];
    }

    public function getLanguageId()
    {
        return $this->property['languageid'];
    }

    public function generateLanguageId()
    {
        //nothing
    }

    public function getEditorProperty_Line($groupName = null, $terminatePreviousGroup = false, $hr = true)
    {
        $groupHead = "";
        $groupTerminator = "";
        if ($groupName || $terminatePreviousGroup) {

            // the group can hide and show on click. the hidden/shown property is stored in a cookie.

            $groupHtml = <<<html
<div class="propertyGroup" data-group-name="$groupName">
    <h2 style='margin:-12px 5px 0;padding:3px;background:lightgray;cursor:pointer;-moz-user-select:none'>
        <span style=padding:5px;font-weight:bold>&equiv;</span>$groupName
    </h2>
    <script>
        let val = $.cookie('formedit_$groupName') == 'true';
        $('[data-group-name="$groupName"] > h2').click(function(){
            $(this).parent().find('> div').slideToggle();
            let v = $.cookie('formedit_$groupName') == 'true';
            $.cookie('formedit_$groupName', !v);
        }).parent().find('> div')[val ? 'show' : 'hide']();
    </script>
    <div style=margin-bottom:0>
        #GROUPCONTENT#
    </div>
</div>
html;
            $group = explode('#GROUPCONTENT#', $groupHtml);
            if ($groupName) {
                $groupHead = $group[0];
            }
            if ($terminatePreviousGroup) {
                $groupTerminator = $group[1];
            }
        }

        $hrHtml = $hr ? "<div class='propertyelementborder'><hr></div>" : "";

        return "$groupTerminator $hrHtml $groupHead";
    }

    public function getEditorProperty_Label($text)
    {
        return '<div class="propertyelementborder">
        '.$text.'
        </div>';
    }

    public function getEditorProperty_Textbox($text, $name, $value = '', $deprecated = false)
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        // we want to preserver user's html stuff.
        $value = htmlentities($value);

        $disabled = $deprecated ? 'disabled' : '';

        $name = $this->name."_".$name;

        return '<div class="propertyelementborder">
        '.$text.':<br>
        <input type="textbox" name="'.$name.'" value="'.$value.'" '.$disabled.'>
        </div>';
    }

    public function getEditorProperty_FileUpload($text, $name, $value = '', $deprecated = false)
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }
        $valueSource = $this->property[$name."_SOURCE"];

        // we want to preserver user's html stuff.
        $value = htmlentities($value);

        $disabled = $deprecated ? 'disabled' : '';

        $name = $this->name."_".$name;
        $e = '<div class="propertyelementborder">
        '.$text.':<br>';
        if ($valueSource != "") {
            $e .= '<img src="'.$valueSource.'" style="max-width:200px; " border="1"><br>
            <input type="checkbox" name="'.$name.'_REMOVE" value="1"> delete file<br>';
        } else {
            $e .= '<input type="hidden" name="'.$name.'" value="1"><br>
            <input type="file" name="'.$name.'_FILE" value="'.$value.'" '.$disabled.'><br>';
        }
        $e .= '</div>';

        return $e;
    }

    public function getEditorProperty_Textarea($text, $name, $value = '')
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        // we want to preserver user's html stuff.
        $value = htmlentities($value);

        $id = "textarea".uniqid("");

        $name = $this->name."_".$name;

        return <<<html
<div class="propertyelementborder">
    $text:<br>
    <textarea id="$id" name="$name" class="uses-tab-btn" style="height:100px;resize:vertical"
        >$value</textarea>
</div>
html;
    }

    public function getEditorProperty_Checkbox($text, $name, $value = false)
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $name = $this->name."_".$name;

        return "<div class='propertyelementborder'>
        <input type='hidden' name='$name' value='0'>
        <input type='checkbox' name='$name' value='1' ".($value == true ? 'checked' : '')."> $text
        </div>";
    }

    public function getEditorProperty_Selectbox($text, $name, $values, $value = "")
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $name = $this->name."_".$name;
        $html = "<div class='propertyelementborder'>
        $text:<br>
        <select name='$name'>";
        foreach ($values as $key => $value2) {
            $html .= '<option value="'.$key.'" ';
            if ($value == $key) {
                $html .= ' selected ';
            }
            $html .= '>'.$value2.'</option>';
        }
        $html .= "</select>
        </div>";

        return $html;
    }


    public function getEditorProperty_SelectboxFiles($text, $name, $value = "")
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $name = $this->name."_".$name;
        $html = "<div class='propertyelementborder'>
        $text:<br>
        <select name='$name'><option value=''></option>";

        $hsconfig = getHsConfig();
        $values = $hsconfig->getFiles();
        if (is_array($values)) {
            //for($x=0;$x<count($values);$x++)
            foreach ($values as $file) {
                $html .= '<option';
                if ($file == $value) {
                    $html .= ' selected ';
                }
                $html .= '>'.$file.'</option>';
            }
        }

        $html .= "</select>
        </div>";

        return $html;
    }

    /**
     * return formular id that selected within the properties of an element
     *
     * @param $sFormularId
     *
     * @return string
     */
    public function getSelectedFormular($sFormularId)
    {
        if ($sFormularId == "#STARTFORM#") {
            $hsConfig = getHsConfig();

            /* change startfrom */
            if ($startForm = $hsConfig->getInterpreterValue("startform")) {
                $sFormularId = $startForm;
            }

            if ( ! $sFormularId) {
                $projectForms = $hsConfig->getInterpreterValue("formulare");
                reset($projectForms);
                $sFormularId = key($projectForms);
            }
        }

        return $sFormularId;
    }

    public function getEditorProperty_SelectboxFormulare($text, $name, $value = "", $canSelectStartForm = false)
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $name = $this->name."_".$name;
        $html = "<div class='propertyelementborder'>
        $text:<br>
        <select name='$name'><option value=''></option>";

        foreach ($_SESSION["editor"] as $key => $value2) {
            $e = unserialize($_SESSION['editor'][$key]['property']);
            $name = $e->getTabName();
            $id = $e->getTabId();
            if ($name == "") {
                $name = $id;
            } else {
                $name .= " (".$id.")";
            }

            $html .= '<option value="'.$id.'" ';
            if ($id == $value) {
                $html .= ' selected ';
            }
            $html .= '>'.$name.'</option>';
        }
        if ($canSelectStartForm) {
            $html .= '<option value="#STARTFORM#" '.('#STARTFORM#' == $value ? ' selected ' : '').'>#STARTFORM#</option>';
        }

        $html .= "</select>
        </div>";

        return $html;
    }

    public function getEditorProperty_SelectboxTabs($text, $name, $value = "")
    {

        //require_once(realpath(dirname(__FILE__) . "/core/hsinit.php"));

        $hsconfig = getHsConfig();
        $d = opendir($hsconfig->getBasePath."elements");
        while ($file = readdir($d)) {
            if (is_file($hsconfig->getBasePath."elements/".$file)) {
                include_once($hsconfig->getBasePath."elements/".$file);
            }
        }
        closedir($d);


        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $name = $this->name."_".$name;
        $html = "<div class='propertyelementborder'>
        $text:<br>
        <select name='$name'><option value=''></option>";


        foreach ($_SESSION["editor"][$this->containerid]['elements'] as $id => $object) {
            //echo $id;
            //echo '<br>';
            $e = unserialize($object);
            if ($e->isparentcontrol == true) {
                //$id=$e->id;
                $name = trim($e->property['name']);
                $tabs = trim($e->property['tabs']);
                if ($tabs != "") {
                    $tabs = explode("|", $tabs);
                    for ($x = 0; $x < count($tabs); $x++) {
                        $tmpid = $this->containerid."_".$id."_".$x;
                        $tmpname = $name.($name != "" ? " - " : "").$tabs[$x];

                        $html .= '<option value="'.$tmpid.'" ';
                        if ($tmpid == $value) {
                            $html .= ' selected ';
                        }
                        $html .= '>'.$tmpname.'</option>';

                    }
                }
            }
        }

        $html .= "</select>
        </div>";

        return $html;

    }


    public function getEditorProperty_SelectboxScriptphp2($text, $name, $value = "")
    {
        //files from folder scriptphp2
        $files = array();

        $hsconfig = getHsConfig();
        if (file_exists($hsconfig->getBasePath."scriptphp2")) {
            $d = opendir($hsconfig->getBasePath."scriptphp2");
            while ($file = readdir($d)) {
                if (is_file($hsconfig->getBasePath."scriptphp2/".$file)) {
                    $files[] = $file;
                    //include_once($hsconfig->getBasePath."scriptphp2/".$file);
                }
            }
            closedir($d);
        }

        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $name = $this->name."_".$name;
        $html = "<div class='propertyelementborder'>
        $text:<br>
        <select name='$name'><option value=''></option>";

        sort($files);

        for ($x = 0; $x < count($files); $x++) {
            $file = $files[$x];
            $html .= '<option value="'.$file.'" ';
            if ($file == $value) {
                $html .= ' selected ';
            }
            $html .= '>'.$file.'</option>';
        }

        $html .= "</select>
        </div>";

        return $html;

    }

    public function getEditorProperty_TextareaPHP($text, $name, $value = '')
    {
        if (isset($this->property[$name])) {
            $value = $this->property[$name];
        }

        $id = "textarea".uniqid("");

        $name = $this->name."_".$name;

        return '<div class="propertyelementborder">
        '.$text.':<br>
        <textarea id="'.$id.'" name="'.$name.'" style="height:100px; ">'.$value.'</textarea>
        </div>
        <script type="text/javascript">
            // handle tab in textarea
            $("#'.$id.'").keydown(function(e) {
                if(e.keyCode === 9) { // tab was pressed
                    // get caret position/selection
                    var start = this.selectionStart;
                    var end = this.selectionEnd;

                    var $this = $(this);
                    var value = $this.val();

                    // set textarea value to: text before caret + tab + text after caret
                    $this.val(value.substring(0, start)
                                + "\t"
                                + value.substring(end));

                    // put caret at right position again (add one for the tab)
                    this.selectionStart = this.selectionEnd = start + 1;

                    // prevent the focus lose
                    e.preventDefault();
                }
            });
        </script>
        ';
    }

    public function setEditorProperty()
    {

        foreach ($_REQUEST as $key => $value) {
            if (substr($key, 0, strlen($this->name."_")) == $this->name."_") {
                $key = substr($key, strlen($this->name."_"));
                //$this->property[$key] = stripslashes($value);

                $aProcessedProperties = $this->_setEditorProperty_processValue($key, $value);
                if (is_array($aProcessedProperties)) {
                    foreach ($aProcessedProperties as $sKey => $sValue) {
                        $this->property[$sKey] = $sValue;
                    }
                }
            }
        }
    }


    protected function _setEditorProperty_processValue($key, $value)
    {
        return [$key => $value];
    }


    /** @var string */
    protected $debugInfo = null;

    public function getDebugInfo()
    {
        return array(
            "id"        => "no-id",
            "name"      => "no-name",
            "debugInfo" => $this->debugInfo,
        );
    }
}
