<?php

class iframe extends basecontrol
{
    public $name="iframe";

    public $editorname = "iFrame";
    public $editorcategorie = "Container";
    public $editorshow = true;
    public $editordescription = 'iFrame, which referenced another form from the project. The current index-value of the form will attach.';

    public function getInterpreterRender()
    {
        $hs = getHsConfig();

        $projectName = $this->property['project'];

        $oldPath = '/../formedit/files/';
        $newPath = '/formeditold/formedit/';
        $projectName = str_replace($oldPath,$newPath,$projectName);

        // test if parent form has saved it´s data
        $bIsNewMode=false;
        if($this->property['readonlyuntilsaved'])
        {
            $oTab = $this->getTab();
            $sql="select count(*) from `".$oTab->getTableName()."` where `".$oTab->getColIndex()."`='{$hs->getIndex1Value()}'";
            if($hs->getScalar($sql)=="0")
                $bIsNewMode=true;
        }

        $css = array(
            "width" => $this->property['fixwidth'] == "0" ? "calc(100% - " . ($this->left * 2) . "px)" : "{$this->width}px",
            "position" => "absolute",
            "left" => "{$this->left}px",
            "top" => "{$this->top}px",
        );
        if (array_key_exists($this->id, $this->ainterpretererrorlist)) {
            $css["background-color"] = "red";
        }
        if ($this->property['autoheight']) {
            $css["overflow"] = "visible";
        } else {
            $css["overflow"] = 'hidden';
            $css["padding-right"] = '2px';
            $css["padding-bottom"] = '2px';
            $css["height"] = "{$this->height}px";
            $css["line-height"] = "{$this->height}px";
        }
        if ($this->property['invisible']) {
            $css["display"] = "none";
        }

        $css = $this->buildCssString($css);

        // always on top of everything the change that the user wants.
        if ($this->property['style']) {
            $css .= $this->property['style'];
        }

        $iframeCss = $this->buildCssString(array(
            "border" => $this->property['borderless'] ? "0px solid #ccc" : "1px solid #ccc",
            "width" => "100%",
            "height" => "100%",
        ));
        $iframeClass = $this->property['autoheight'] ? "iframeautoheight" : "";
        $tabIndex = $this->property['taborder'];



        if ($this->property['readonly']) {
            //display this, when you was set as "readonly"
            $e = <<<html
            <div data-customeridbox="{$this->getCustomerId()}" data-hasparentcontrol="{$this->getParentControl()}" 
                class="{$this->property['classname']}" id="$this->id" style="{$this->getParentControlCss()};$css">
                <div data-customerid="{$this->getCustomerId()}" id="iframe$this->id" style="$iframeCss" class="$iframeClass" tabindex="$tabIndex">
                    <div style="text-align:center; font-size:16px; color:darkgray; ">Disabled</div>
                </div>
            </div>
html;
        }
        elseif($this->property['readonlyuntilsaved'] && $bIsNewMode)
        {
            //display this, when you was set as "disabled until the parent form was successfully saved"
            $e = <<<html
            <div data-customeridbox="{$this->getCustomerId()}" data-hasparentcontrol="{$this->getParentControl()}" 
                class="{$this->property['classname']}" id="$this->id" style="{$this->getParentControlCss()};$css">
                <div data-customerid="{$this->getCustomerId()}" id="iframe$this->id" style="$iframeCss" class="$iframeClass" tabindex="$tabIndex">
                    <div style="text-align:center; font-size:16px; color:darkgray; ">You have to save first</div>
                </div>
            </div>
html;
        }
        else
        {
            // if project doesn't end in cpf (and is not an empty string), then we must add a cpf extension to it.
            if (preg_match('~.(?<!\.cpf)$~', $projectName)) {
                $projectName .= ".cpf";
            }

            $project = $hs->getProjectBaseDir() . "/$projectName";
            $project = 'project='.realpath($project); // we were having issues with modSec with paths that contain ../../ in their paths. this solves it.
            // we preffer relative paths to the modules folder.
            $project = str_replace(shopInterface::getInstance()->getModulesDir(), "", $project);

            $iframeSrc = $hs->getBaseUrl() . "/interpreter.php?$project&index2value={$hs->getIndex1Value()}";
            $iframeSrc.="&languageedit=".$hs->getLanguageEdit()."&languageadmin=".$hs->getLanguageAdmin();
            $urlParameter = trim($this->property['urlparameter']);
            if ($urlParameter) {
                $iframeSrc .= '&' . $hs->parseSQLString($urlParameter);
            }
            $scrolling = $this->property['autoheight'] || $this->property['borderless'] ? "no" : "yes";

            // dont load iframe immediately if it is inside a tab.
            $srcAttr = $this->getParentControl() ? "data-src" : "src";

            $script = !$this->property['autoheight'] ? "" : <<<html
            <script>
              $("#iframe$this->id").iframeAutoHeight({debug: false, diagnostics: false, heightOffset:50});
            </script>
html;

            $e = <<<html
            <div data-customeridbox="{$this->getCustomerId()}" data-hasparentcontrol="{$this->getParentControl()}" 
                class="{$this->property['classname']}" id="$this->id" style="{$this->getParentControlCss()};$css">
            
                <iframe data-customerid="{$this->getCustomerId()}" $srcAttr="$iframeSrc" scrolling="$scrolling" frameborder="0"
                    id="iframe$this->id" style="$iframeCss" class="$iframeClass" tabindex="$tabIndex"></iframe>
            </div>
            $script
html;
        }

        return $e;
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }

    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        //$html.=parent::getEditorProperty_SelectboxFiles("Project",'project');
        $html .= parent::getEditorProperty_Textbox("Project path relative from this project", 'project');
        $html .= parent::getEditorProperty_Textbox("Url-Parameter (separate by &, you can use #INDEX1#,#INDEX2#.) 
<br>Special parameters i.e.:
<ul>
<li><b>startformname=FORMULAR_NAME</b>: Represents which form should be used on the formedit</li>
<li><b>through=table_name</b>: Defines if the formedit should use a through table to get the element(s), i.e. cpcontact should pass through emprovider2cpcontact, then the through_table_name should be emprovider. 
<br>This param will replace on the target formedit project the SQL statements where have a match on <b>#THROUGH#</b></li>
</ul>", 'urlparameter');
        $html .= parent::getEditorProperty_Checkbox("Auto-height", 'autoheight');
        $html .= parent::getEditorProperty_Checkbox("Frameless", 'borderless');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Disabled", 'readonly');
        $html .= parent::getEditorProperty_Checkbox("Disabled until parent form successfully saved", 'readonlyuntilsaved');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html .= parent::getEditorProperty_Line();
        $html .= parent::getEditorPropertyFooter(true, false, true);
        return $html;
    }

}
