<?php

class selectbox_file extends basecontrol
{
    public $name = "selectbox_file";
    public $editorname = "Selectbox File";
    public $editorcategorie = "Database Items";
    public $editorshow = true;
    public $editordescription = 'Select-box where the user can select a file from a folder.';

    /**
     * @param string &$shopRootDir  out. shop root dir used.
     * @return string
     * @throws Exception when folder is not found.
     */
    protected function getFolderPath(&$shopRootDir = null)
    {
        $config = getHsConfig();

        $path = $this->property['folderpath'];

        if (!$path) {
            throw new Exception("Directory is empty!");
        }

        $shopRootDir = rtrim($config->getShopRootDir(), "/");
        $path = "$shopRootDir/$path";
        $path = realpath($path);

        if (file_exists($path) && is_dir($path)) {
            //$path = rtrim($path, "/") . "/";
        } else {
            throw new Exception("Select box file, directory does not exist or is not a directory!: $path");
        }

        return $path;
    }

    /*
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        return parent::interpreterSaveEdit($table, $colindex, $indexvalue);
    }
    */

    public function getInterpreterRender()
    {
        // used by some elements to position in page.
        $sizeCss = array(
            "width" => $this->width . "px",
            "height" => $this->height . "px",
            "line-height" => $this->height . "px",
        );
        $positionCss = $sizeCss + array(
            "position" => "absolute",
            "left" => $this->left . "px",
            "top" => $this->top . "px",
        );

        try {
            // where to get the options that will be displayed in the select box
            $path = $this->getFolderPath($shopRootDir);
            $shopRelativePath = preg_replace("~^$shopRootDir/~", "", $path);

            // the currently selected item in the select box
            $value = parent::getInterpreterRequestValue();

            // find all files inside the folder.
            $files = glob("$path/*.php");
            asort($files);

            $customId = $this->getCustomerId();
            $readOnly = $this->property['readonly'] == "1";

            $e = "";
            if ($readOnly) {
                $e .= "<input data-customerid='$customId' type=hidden name=$this->id value='$value'>\n";
            }

            $css = $positionCss + array(
                "display" => $this->property['invisible'] == "1" ? "none" : null,
            );

            $cssString = $this->buildCssString($css);
            $parentCss = rtrim(trim($this->getParentControlCss()), ";");
            $cssString = "$parentCss;$cssString;" . $this->property['style'];

            $attrs = array(
                "data-customeridbox" => $customId ?: null,
                "data-hasparentcontrol" => $this->getParentControl() ?: null,
                "class" => $this->property['classname'] ?: null,
                "id" => $this->id,
                "style" => $cssString
            );
            $attrsString = $this->buildHtmlAttributes($attrs);

            $e .= "<div $attrsString>\n";

            $selectCss = $sizeCss + array(
                "vertical-align" => "middle",
                "border" => "1px solid #dddddd",
                "border-color" => array_key_exists($this->id, $this->ainterpretererrorlist) ? "red" : null,
                "opacity" => $readOnly ? "0.5" : null,
            );
            $selectCssString = $this->buildCssString($selectCss);

            $selectAttrs = array(
                "data-customerid" => $readOnly ? $customId : null,
                "name" => $this->id,
                "style" => $selectCssString,
                "tabindex" => $this->property['taborder'] ?: null,
                "readonly" => $readOnly ?: null,
                "disabled" => $readOnly ?: null,
                "title" => "Base path is: $shopRelativePath",
            );
            $selectAttrsString = $this->buildHtmlAttributes($selectAttrs);

            $e .= "<select $selectAttrsString>\n";
            if ($this->property['blankitem'] == '1') {
                $e .= "<option value=''></option>\n";
            }

            //$saveWithFolderPath = $this->property['savewithfolderpath'] == '1';
            foreach ($files as $fullFilePath) {
                $shopRelativeFilePath = preg_replace("~^$shopRootDir/~", "", $fullFilePath);
                $optionAttrs = array(
                    "value" => $shopRelativeFilePath,
                    "selected" => $value == $shopRelativeFilePath ?: null,
                );
                $optionsAttrString = $this->buildHtmlAttributes($optionAttrs);
                $displayName = basename($shopRelativeFilePath);
                $e .= "<option $optionsAttrString>$displayName</option>\n";
            }
            $e .= "</select></div>\n";

        } catch (Exception $ex) {

            $e = $ex->getMessage();
            $errorCss = $positionCss + array(
                "color" => "red",
            );
            $errorCssString = $this->buildCssString($errorCss);
            $e = "<div style='$errorCssString'>$e</div>";
        }
        return $e;
    }

    public function getEditorProperty()
    {
        $html ='';
        $html .= $this->getEditorPropertyHeader();
        $html .= $this->getEditorProperty_Textarea("Path to the Folder that should display (relative to shop root)",'folderpath');
        $html .= $this->getEditorProperty_Checkbox("Show blank item",'blankitem',1);
        $html .= $this->getEditorProperty_Line();
        $html .= $this->getEditorProperty_Line();
        $html .= $this->getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html .= $this->getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html .= $this->getEditorProperty_Line();
        $html .= $this->getEditorProperty_Checkbox("Readonly",'readonly');
        $html .= $this->getEditorPropertyFooter();
        return $html;
    }
}
