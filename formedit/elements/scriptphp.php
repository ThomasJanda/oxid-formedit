<?php

class scriptphp extends basecontrol
{
    var $name = "scriptphp";

    var $editorname = "ScriptPHP";
    var $editorcategorie = "Script Elements";
    var $editorshow = true;
    var $editordescription = 'PHP class which can react to all events of a form/element. The script must in the folder "scriptphp" and must inherit the base class "interfacephp.php" from the same folder. The filename must be the class name.';

    /** @var interfacephp  */
    var $phpclass = null;

    /**
     * @return string|null
     * @throws Exception
     */
    public function interpreterInit()
    {
        $returnvalue = parent::interpreterInit();
        $this->includeScriptClass();

        if ($phpclassname = $this->property['phpclassname']) {
            if (!class_exists($phpclassname)) {
                throw new Exception("scriptphp class not found: $phpclassname");
            }

            $this->phpclass = new $phpclassname;
        }
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterInit();
        }

        return $returnvalue;
    }

    public function interpreterBeforeDelete()
    {
        $returnvalue = parent::interpreterBeforeDelete();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeDelete();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterDelete($table, $colindex, $indexvalue)
    {
        $returnvalue = parent::interpreterDelete($table, $colindex, $indexvalue);
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterDelete($table, $colindex, $indexvalue);
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterDelete()
    {
        $returnvalue = parent::interpreterAfterDelete();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterDelete();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBeforeBulkDelete()
    {
        $returnvalue = parent::interpreterBeforeBulkDelete();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeBulkDelete();
        }

        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBulkDelete($table, $colindex, $indexvalue)
    {
        $returnvalue = parent::interpreterBulkDelete($table, $colindex, $indexvalue);
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBulkDelete($table, $colindex, $indexvalue);
        }

        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterBulkDelete()
    {
        $returnvalue = parent::interpreterAfterBulkDelete();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterBulkDelete();
        }

        //echo __FUNCTION__;
        return $returnvalue;
    }


    public function setInterpreterIsFirstNew()
    {
        $returnvalue = parent::setInterpreterIsFirstNew();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->setInterpreterIsFirstNew();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function setInterpreterIsNew()
    {
        $returnvalue = parent::setInterpreterIsNew();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->setInterpreterIsNew();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBeforeProveNew()
    {
        $returnvalue = parent::interpreterBeforeProveNew();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeProveNew();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterProveNew($table, $colindex, $indexvalue)
    {
        $returnvalue = parent::interpreterProveNew($table, $colindex, $indexvalue);
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterProveNew($table, $colindex, $indexvalue);
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterProveNew()
    {
        $returnvalue = parent::interpreterAfterProveNew();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterProveNew();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBeforeSaveNew()
    {
        $returnvalue = parent::interpreterBeforeSaveNew();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeSaveNew();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $returnvalue = parent::interpreterSaveNew($table, $colindex, $indexvalue);
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterSaveNew($table, $colindex, $indexvalue);
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterSaveNew()
    {
        $returnvalue = parent::interpreterAfterSaveNew();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterSaveNew();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }


    public function setInterpreterIsFirstEdit()
    {
        $returnvalue = parent::setInterpreterIsFirstEdit();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->setInterpreterIsFirstEdit();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function setInterpreterIsEdit()
    {
        $returnvalue = parent::setInterpreterIsEdit();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->setInterpreterIsEdit();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBeforeProveEdit()
    {
        $returnvalue = parent::interpreterBeforeProveEdit();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeProveEdit();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterProveEdit($table, $colindex, $indexvalue)
    {
        $returnvalue = parent::interpreterProveEdit($table, $colindex, $indexvalue);
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterProveEdit($table, $colindex, $indexvalue);
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterProveEdit()
    {
        $returnvalue = parent::interpreterAfterProveEdit();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterProveEdit();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBeforeSaveEdit()
    {
        $returnvalue = parent::interpreterBeforeSaveEdit();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeSaveEdit();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $returnvalue = parent::interpreterSaveEdit($table, $colindex, $indexvalue);
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterSaveEdit($table, $colindex, $indexvalue);
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterSaveEdit()
    {
        $returnvalue = parent::interpreterAfterSaveEdit();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterSaveEdit();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }


    public function interpreterBeforeRender()
    {
        $returnvalue = parent::interpreterBeforeRender();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterBeforeRender();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function getInterpreterRender()
    {
        $returnvalue = parent::getInterpreterRender();
        $e = '
        <div class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:14px; ">
        ';
        if ($this->phpclass) {
            $e .= $this->phpclass->getInterpreterRender();
        }
        $e .= '
        </div>
        ';

        return $e;

    }

    public function interpreterAfterRender()
    {
        $returnvalue = parent::interpreterAfterRender();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterAfterRender();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }


    public function interpreterFinish()
    {
        $returnvalue = parent::interpreterFinish();
        if ($this->phpclass) {
            $returnvalue = $this->phpclass->interpreterFinish();
        }
        //echo __FUNCTION__;
        return $returnvalue;
    }


    public function getEditorProperty()
    {
        $html = '';
        $html .= parent::getEditorPropertyHeader();
        $html .= parent::getEditorProperty_Textarea("Description", 'beschreibung');
        $html .= parent::getEditorProperty_Textbox("Classname (Filename have to be the same as the classname + .php)", 'phpclassname');
        $html .= parent::getEditorProperty_Textbox("Relative path (Leave it empty, if the files is in the 'scriptphp' of this project, otherwise relatvie path to the script include 'scriptphp'.)", 'relativepath');
        $html .= parent::getEditorPropertyFooter(true, false, false, false);
        return $html;
    }

    public function includeScriptClass()
    {
        $hsConfig = getHsConfig();
        $projectBaseDir = $hsConfig->getProjectBaseDir();
        $cl = trim($this->property['phpclassname']);

        // this just includes the class if it was in an external directory, because all scripts that live in this same
        // formedit folder are already included.
        if ($rp = trim($this->property['relativepath'])) {
            $p = "$projectBaseDir/$rp/$cl.php";
            $p = str_replace("//","/",$p);
            //$p = realpath($p);
            //echo $p."<br>";
            if (file_exists($p)) {
                require_once $p;
            } else {
                echo "SCRIPTPHP: can not find file ($p)";
            }
        }

        $otherScripts = glob("$projectBaseDir/scriptphp/*.php");
        foreach ($otherScripts as $otherScript) {
            //echo $otherScript."<br>";
            require_once $otherScript;
        }

        //die("");
    }
}
