<?php

class fileupload2 extends basecontrol
{
    var $name="fileupload2";

    var $editorname="Fileupload2";
    var $editorcategorie="Database Items";
    var $editorshow=true;
    var $editordescription='Allows files to be uploaded. The file name is stored in a database column.';


    protected function canonicalize($address)
    {
        $address = str_replace("//","||||",$address);
        $address = explode('/', $address);
        $keys = array_keys($address, '..');

        foreach($keys AS $keypos => $key)
        {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }

        $address = implode('/', $address);
        $address = str_replace('./', '', $address);

        $address = str_replace("||||","//",$address);

        return $address;
    }

    public function getInterpreterRender()
    {
        if(parent::getInterpreterIsFirstNew()) {
            $value = "";
        } else {
            $value = parent::getInterpreterRequestValue();
        }

        $hsconfig=getHsConfig();

        if($projectBaseDir = $hsconfig->getProjectBaseDir()) {
            $rootpath = $hsconfig->realpath("$projectBaseDir/" . $this->property['rootpath']);
        }
        else {
            $rootpath = $this->property['rootpath'];
        }

        if ($projectBaseUrl = $hsconfig->getProjectBaseUrl()) {
            $urlpath = $this->canonicalize($projectBaseUrl . "/" . $this->property['rootpath']);
        }
        else {
            $urlpath = $this->property['rootpath'];
        }

        if ($this->property['debugmode'] == "1") {
            echo "ROOTPATH: " . $rootpath . "<br>";
            echo "ROOTURL: " . $urlpath . "<br>";
        }

        $e='<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.(array_key_exists($this->id,$this->ainterpretererrorlist)?'border-color:red; ':'').' '.$this->property['style'].' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <table>
                <tr>
                    <td>';
        //<textarea id="textbox_'.$this->id.'" disabled>'.$value.'</textarea>
        $e.='<textarea id="textbox_'.$this->id.'" disabled style="border:1px solid #dddddd; width:100%; height:20px; line-height:20px; vertical-align:middle; white-space:nowrap; resize: none; font-family:verdana; overflow:hidden; font-size:12px; " wrap="soft">'.$value.'</textarea>';
        $e.='<td>
            <td><button type="button" id="delete_'.$this->id.'">X</button></td>
            <td><button type="button" id="button_'.$this->id.'">...</button></td>
        </tr>
        ';

        $link="";
        if($value!="")
            $link=$urlpath."/".$value;

        if($link!="")
            $e.='<tr><td colspan="2"><a id="'.$this->id.'link" href="'.$link.'" target="_blank">'.$value.'</a></td></tr>';

        $e.='
            </table>
            <input type="hidden" id="hidden_'.$this->id.'" name="'.$this->id.'" value="'.$value.'">
            <script type="text/javascript">
                $("#button_'.$this->id.'").button().click(function() {
                    returnfunction = "fileupload2Return'.$this->id.'";
                    windowname = "Load File";
                    picurl="'.$urlpath.'";
                    picpath="'.$rootpath.'";
                    sessionname="fileupload2'.$this->id.'";
                    fileext="'.$this->property['fileext'].'";
                    popup = window.open("filebrowseropen/index.php?returnfunction=" + returnfunction + "&picurl=" + picurl + "&picpath=" + picpath + "&sessionname=" + sessionname + "&fileext=" + fileext, windowname, "height=430,width=900, scrollbars=1, resizable=1");
                    popup.focus();
                });
                $("#delete_'.$this->id.'").button().click(function() {
                    $("#textbox_'.$this->id.'").val("");
                    $("#hidden_'.$this->id.'").val("");
                });
                function fileupload2Return'.$this->id.'(path)
                {
                    path = path.substr("'.$rootpath.'".length + 1);
                    $("#textbox_'.$this->id.'").val(path);
                    $("#hidden_'.$this->id.'").val(path);
                }
            </script>
        </div>';

        return $e;
    }



    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Relative path to the folder where the files stored starting on the project root folder. (no '/' at the start and end)",'rootpath','');
        //$html.=parent::getEditorProperty_Textbox("Relative Html link to the upload path start from the root directory of this project (../../../out/pictures/wysiwigpro)",'urlpath','');
        $html.=parent::getEditorProperty_Textbox("File extensions that can select (sep with ;)",'fileext','');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("CSS-style (deprecated)",'style');
        $html.=parent::getEditorProperty_Line();

        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorProperty_Line();

        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
}

?>
