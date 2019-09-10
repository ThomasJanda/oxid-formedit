<?php

class selectbox_findfiles extends basecontrol
{
    var $name="selectbox_findfiles";

    var $editorname="Selectbox Find Files";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Select box in which the user can select a file from a search pattern';


    protected function getRootPath()
    {
        $hsConfig = getHsConfig();
        $path = realpath($hsConfig->getProjectBaseDir() . "/" . $this->property['cutpath']);
        $path = rtrim($path, "/") . "/";
        return $path;
    }

    protected function getFiles()
    {
        $hsConfig = getHsConfig();

        $allfiles = array();

        $rootpath = $this->getRootPath();
        $allsearchpattern = explode("\n",$this->property['searchpattern']);
        foreach($allsearchpattern as $searchpattern) {
            if ($searchpattern != "") {
                $path = $hsConfig->getProjectBaseDir() . "/$searchpattern";
                if(isset($this->property['onlydirectories']) && $this->property['onlydirectories']=="1")
                    $files = glob($path, GLOB_ONLYDIR);
                else
                    $files = glob($path);

                if (is_array($files)) {
                    $allfiles = array_merge($allfiles, $files);
                }
            }
        }

        $files2=array();
        foreach($allfiles as $file)
        {
            $p = realpath($file);
            $p = str_replace($rootpath,"",$p);
            $files2[]=$p;
        }
        $allfiles = $files2;
        asort($allfiles);

        return $allfiles;
    }

    public function getInterpreterRender()
    {
        $value=parent::getInterpreterRequestValue();

        $files = $this->getFiles();

        $e="";
        if($this->property['readonly']=="1")
        {
            $e.='<input data-customerid="'.$this->getCustomerId().'" type="hidden" name="'.$this->id.'" value="'.$value.'">';
        }

        $e.= '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">';
        $e.='
            <select 
                ';
        if($this->property['readonly']=="1")
        {
            $e.='data-customerid="'.$this->getCustomerId().'" ';
        }
        $e.='
                name="'.$this->id.'" 
                style="vertical-align:middle; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; border:1px solid #dddddd; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' '.($this->property['readonly']=="1"?'opacity:0.5;':"").' "
                tabindex="'.$this->property['taborder'].'"
                '.($this->property['readonly']=="1"?'readonly="readonly" disabled="disabled"':"").'
            >
            ';
        if($this->property['blankitem']=='1')
        {
            $e.="<option value=''></option>";
        }

        $aDontDisplay = $this->property['dontdisplay']??"";
        $aDontDisplay = explode("\n",$aDontDisplay);
        $aDontDisplay = array_map('trim', $aDontDisplay);
        $aDontDisplay = array_unique($aDontDisplay);

        foreach($files as $file)
        {
            if(!in_array($file,$aDontDisplay))
                $e.="<option value='".$file."' ".($file==$value?'selected':'').">".$file."</option>";
        }
        $e.='
            </select>
        </div>';
        return $e;
    }

    /*
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $s=false;
        if(isset($this->property['datenbankspalte']))
        {
            $s['col']=$this->property['datenbankspalte'];
            $s['value']=$this->getInterpreterRequestValueForDb();
        }
        return $s;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $s=false;
        if(isset($this->property['datenbankspalte']))
        {
            $s['col']=$this->property['datenbankspalte'];
            $s['value']=$this->getInterpreterRequestValueForDb();
        }
        return $s;
    }
    */


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textarea("Search pattern for the files you like to display. Pattern have to be compatible with the glob command of php. This path have to start from the project files. ('../../*/cronjobs/*.php'). Each pattern per line.",'searchpattern');
        $html.=parent::getEditorProperty_Textbox("Part of the path that cut for saving. Relative path from the project folder (e. g. '../../')",'cutpath');
        $html.=parent::getEditorProperty_Checkbox("Show blank item",'blankitem',1);
        $html.=parent::getEditorProperty_Checkbox("Only directories",'onlydirectories',0);
        $html.=parent::getEditorProperty_Textarea('This strings (folders, files) shoundn´t display in the selectbox e. g. inc. (Each per line)', 'dontdisplay');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Required",'pflichtfeld');
        $html.=parent::getEditorProperty_Textbox("Errormessage",'fehlermeldung','is required');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorPropertyFooter(  true,
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            true);
        return $html;
    }
}

?>
