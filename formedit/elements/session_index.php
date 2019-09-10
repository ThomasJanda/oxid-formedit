<?php

class session_index extends basecontrol
{
    var $name="session_index";

    var $editorname="Session Index";
    var $editorcategorie="Interform";
    var $editorshow=true;
    var $editordescription='Can write one of the values ​​into the session.<br>
     The value may then be accessed in the queries <br>
     The data is available on all forms and can <br>
     overwrite each other. Therefore, the Form Customer ID and <br>
     Customer ID names should be unique as possible. <br>
     #SESSION.FORMCUSTOMERID.ELEMENTCUSTOMERID# <br>
     FORMCUSTOMERID = Customer ID of the form in which this element is <br>
     NAME OF ELEMENT = Customer ID of this element <br>
    ';
    
    
    
    private $hasset=false;

    public function interpreterInit()
    {
        $hsconfig=getHsConfig();
        $oTab = $hsconfig->getTab();
        $oOldTab = $hsconfig->getOldTab();
        if($oTab)
        {
            $wert="";
            if($this->property['value']==0)
                $wert=$hsconfig->getIndex1Value();
            if($this->property['value']==1)
                $wert=$hsconfig->getIndex2Value();
            if($this->property['value']==2)
                $wert=$hsconfig->getKennzeichen1Value();

            if($this->property['viewonly']==0)
            {
                /*
                if($oOldTab)
                echo $oOldTab->getTabId()." ".$this->property['formularbefore'];
                */
                $sFormularIdBefore=$this->property['formularbefore'];
                $sFormularIdBefore=$this->getSelectedFormular($sFormularIdBefore);
                if($sFormularIdBefore=="" || ($oOldTab && $oOldTab->getTabId()==$sFormularIdBefore))
                {
                    $_SESSION["interpreter"]['interfromulardata'][$oTab->getTabCustomerId().".".$this->property['customerid']]=$wert;   
                    $this->hasset=true;
                }
                     
            }            
        }
    }
    public function getInterpreterRender()
    {
        /*
        $e="";
        $hsconfig=getHsConfig();
        $oTab = $hsconfig->getTab();
        
        $wert="";
        if($this->property['value']==0)
            $wert=$hsconfig->getIndex1Value();
        if($this->property['value']==1)
            $wert=$hsconfig->getIndex2Value();
        if($this->property['value']==2)
            $wert=$hsconfig->getKennzeichen1Value();

        if($this->property['viewonly']==0)
        {
            $_SESSION["interpreter"]['interfromulardata'][$oTab->getTabCustomerId().".".$this->property['customerid']]=$wert;    
        }
        */
        
        if($this->property['debugmode']==1)
        {
            $hsconfig=getHsConfig();
            $oTab = $hsconfig->getTab();
            $wert=$_SESSION["interpreter"]['interfromulardata'][$oTab->getTabCustomerId().".".$this->property['customerid']];
            $var="#SESSION.".$oTab->getTabCustomerId().".".$this->property['customerid']."#";
            
            $e = '<div data-customeridbox="'.$this->getCustomerId().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="'.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; ">
                '.$this->property['bezeichnung'].':<br>
                '.$var.'<br>
                '.$wert.'<br>
                '.($this->hasset==true?'<b>NEU GESETZT</b>':'').'
            </div>';
        }
        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox('Title','bezeichnung');
        $html.=parent::getEditorProperty_Selectbox("Value",'value',array(0=>'#INDEX1#',1=>'#INDEX2#',2=>'#KENNZEICHEN1#'),0);
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_SelectboxFormulare("Only set value, if parent form is:",'formularbefore','#STARTFORM#',true);
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorProperty_Checkbox("Show only, do not modify session",'viewonly','0');
        $html.=parent::getEditorPropertyFooter(true,false,false,false);
        
        return $html;
    }
}

?>