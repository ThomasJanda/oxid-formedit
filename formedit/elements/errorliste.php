<?php

class errorliste extends basecontrol
{
    var $name="errorliste";

    var $editorname="Errorlist";
    var $editorcategorie="Error";
    var $editorshow=true;
    var $editordescription='Error in list of the error messages are displayed.';

    public function getInterpreterRender()
    {
        $e="";
        if(count($this->ainterpretererrorlist)!=0)
        {
            $e = '<div data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().' '.$this->property['css'].' border:1px solid red; color:red; position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; overflow-x: hidden; overflow-y: auto; ">
                <ul>';
                foreach($this->ainterpretererrorlist as $key=>$value)
                {
                    $e.='<li>'.$value.'</li>';
                }
           $e.='
                </ul>
            </div>';
        }
        return $e;
    }

    protected $_sEditorElementCss="background-color:red; color:white;";
    /*
    public function getEditorRender($text="")
    {

        $e=parent::getEditorRender($text);
        $e = '<div class="element" id="'.$this->id.'" style="background-color:red; color:white; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; ">
            <input type="hidden" name="classname" value="'.get_class($this).'">
            <input type="hidden" name="containerid" value="'.$this->containerid.'">
            &nbsp;'.($text!=""?$text." (":"").$this->editorcategorie.' - '.$this->editorname.($text!=""?")":"").'
        </div>';
        return $e;
    }
    */

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorPropertyFooter(true,false,false,true);
        return $html;
    }

}

?>