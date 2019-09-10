<?php

class fileupload extends basecontrol {
    var $name = "fileupload";

    var $editorname        = "Fileupload";
    var $editorcategorie   = "Database Items";
    var $editorshow        = true;
    var $editordescription = 'Allows files to be uploaded. The file name is stored in a database column.';


    public function getInterpreterRender() {
        $value = "";
        if (parent::getInterpreterIsFirstNew()) {
            $value = "";
        } else {
            $value = parent::getInterpreterRequestValue();
        }

        $urlpath = $this->getUrlPath();

        $link = "";
        if ($value != "")
            $link = $urlpath . $value;

        $e =
            '<div data-customeridbox="' . $this->getCustomerId() . '" data-hasparentcontrol="' . $this->getParentControl() . '" class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->getParentControlCss() . ' border:1px solid #dddddd;  ' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'border-color:red; ' : '') . ' ' . ($this->property['readonly'] == "1" ? 'opacity:0.5;' : "") . ' ' . ($this->property['invisible'] == "1" ? ' display:none; ' : '') . '">
            <table style="width:' . ($this->width) . 'px; table-layout:fixed; ">
                ';

        if ($value != "") {
            $e .= '<tr><td><a id="' . $this->id . 'link" href="' . $link . '" target="_blank">' . $value . '</a>';

            if ($this->property['readonly'] != "1") {
                $e .= '<td style="width:40px; "><button type="button" id="' . $this->id . 'button">X</button></td>';
            }
            $e .= '</tr>';
        }

        if ($this->property['readonly'] != "1") {
            $e .= '<tr><td colspan="2"><input  type="file" name="' . $this->id . 'file" style="vertical-align:middle; width:' . ($this->width) . 'px; " ></td></tr>';
        }

        $e .= '</table>
            <input type="hidden" id="' . $this->id . 'hidden" name="' . $this->id . '" value="' . str_replace('"', "''", $value) . '">
            <input type="hidden" id="' . $this->id . 'hiddenold" name="' . $this->id . 'hiddenold" value="' . str_replace('"', "''", $value) . '">
            <input type="hidden" id="' . $this->id . 'hiddendel" name="' . $this->id . 'hiddendel" value="0">
        </div>
        <script type="text/javascript">
            $("#' . $this->id . 'button").button().click(function() {
                $("#' . $this->id . 'hiddendel").val("1");
                $("#' . $this->id . 'link").css("display","none");
            });
        </script>';
        return $e;
    }

    private $_SaveFileName = "";

    public function interpreterProve($table, $colindex, $indexvalue) {
        parent::interpreterProve($table, $colindex, $indexvalue);
        $returnValue = [];

        $del         = $_REQUEST[$this->id . 'hiddendel'];
        $currentFile = $_REQUEST[$this->id . 'hiddenold'];

        //'rootpath' is deprecated, now we use 'relativepath'
        $path = $this->getPath();

        $this->_SaveFileName = $currentFile;
        if ($del == "1") {
            $file = $path . $currentFile;//Use the relative path if not empty, or root path otherwise
            if (file_exists($file))
                @unlink($file);

            $this->_SaveFileName = "";
        }

        if (isset($_FILES[$this->id . 'file']['name']) == true && $_FILES[$this->id . 'file']['name'] != "") {
            $file = $path . $currentFile;//Use the relative path if not empty, or root path otherwise
            if (file_exists($file))
                @unlink($file);

            $file = $_FILES[$this->id . 'file']['tmp_name'];

            $fileNameNew = $indexvalue . "_" . $_FILES[$this->id . 'file']['name'];
            $fileNew     = $path . $fileNameNew;//Use the relative path if not empty, or root path otherwise

            if (!is_dir(($path))) {//Create the path if not exist
                mkdir($path, 0777, true);
            }

            if (move_uploaded_file($file, $fileNew) == false) {
                return [$this->id => $this->property['fehlermeldung'] . "12"];
            }
            $this->_SaveFileName = $fileNameNew;
        }

        if ($this->property['pflichtfeldnotempty'] == 1 && $this->_SaveFileName == "") {
            return [$this->id => $this->property['fehlermeldungpflichtfeld']];
        }

        return $returnValue;
    }

    public function interpreterSaveNew($table, $colindex, $indexvalue) {
        $s = parent::interpreterSaveNew($table, $colindex, $indexvalue);
        if($s)
        {
            $hsConfig = getHsConfig();
            $s['value'] = "'" . $hsConfig->escapeString($this->_SaveFileName) . "'";
        }
        return $s;
    }

    public function interpreterSaveEdit($table, $colindex, $indexvalue) {
        $s = parent::interpreterSaveEdit($table, $colindex, $indexvalue);
        if($s)
        {
            $hsConfig = getHsConfig();
            $s['value'] = "'" . $hsConfig->escapeString($this->_SaveFileName) . "'";
        }
        return $s;
    }

    public function interpreterDelete($table, $colindex, $indexvalue) {

        $currentFile = parent::getInterpreterRequestValue();
        $path        = $this->getPath();

        if ($currentFile != "") {
            $file = $path . $currentFile;
            if (file_exists($file))
                @unlink($file);
        }
    }

    /**
     * Retrieves the path, searching on the relative or root path properties.
     *
     * @return mixed|null|string
     */
    protected function getPath() {
        //If the 'base_path' offset exists and is not empty, return this as the full path
        if ( $this->property->offsetExists( 'base_path' ) && $this->property['base_path'] !== '' ) {
            return base_path( rtrim( $this->property['base_path'], '/' ) . '/' );
        }

        //Search for a relative path defined on the project
        if ( $this->property->offsetExists( 'relativepath' ) && $this->property['relativepath'] != '' ) {
            return public_path( rtrim( $this->property['relativepath'], '/' ) . '/' );
        }

        //Not base path nor relative path, then use root path
        return rtrim( rtrim( $this->property['rootpath'], '/' ) . '/' );
    }

    protected function getUrlPath() {
        //Use the relative path to build the URL
        if ( $this->property->offsetExists( 'relativepath' ) && $this->property['relativepath'] != '' ) {
            return asset( rtrim( $this->property['relativepath'], '/' ) . '/' );
        }

        $urlPath = rtrim( $this->property['urlpath'], '/' ) . '/';

        return $urlPath;
    }

    public function getEditorProperty() {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        $html .= parent::getEditorProperty_Textbox("<span style='color:red;'>(<b>Deprecated</b>, use the relative path field)</span><br>Absolute upload path from the root directory of the server", 'rootpath', '/', true);
        $html .= parent::getEditorProperty_Textbox("<span style='color:red;'>(<b>Deprecated</b>, use the relative path field)</span><br>Html link to the upload path with 'http://' at the beginning", 'urlpath', 'http://', true);
        $html .= parent::getEditorProperty_Textbox("Relative path where the uploaded file will be saved <br>(This path will be prefixed with the route to the public folder)", 'relativepath', '');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Textbox("Errormessage", 'fehlermeldung', 'Error in Upload');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Required", 'pflichtfeldnotempty');
        $html .= parent::getEditorProperty_Textbox("Errormessage required", 'fehlermeldungpflichtfeld', 'Upload is required');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Readonly", 'readonly');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorPropertyFooter();
        return $html;
    }

    public function getFileName() {
        return $this->_SaveFileName;
    }


    public function getFile() {
        return $_FILES[$this->id . 'file'];
    }
}
