<?php

class label_user extends basecontrol
{
    var $name="label_user";

    var $editorname="Label user";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Simple text field which always output the current user';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();

        $sqlstring="SELECT concat(lvusers.cpfirst_name, ' ', lvusers.cplast_name)
            FROM lvusers
            where lvusers.f_embaseuser=@f_embaseuser";
        $bezeichnung = $hsconfig->getScalar($sqlstring);

        $e = '<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->getParentControlCss().'position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.$this->property['css'].' '.($this->property['invisible']=="1"?' display:none; ':'').'">
            '.$bezeichnung.'
            '.($this->property['debugmode']=="1"?'<br>'.$sqlstring:'').'
        </div>';
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("CSS-Style",'css');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}
