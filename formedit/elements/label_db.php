<?php

class label_db extends basecontrol
{
    var $name="label_db";

    var $editorname="Label DB";
    var $editorcategorie="Style";
    var $editorshow=true;
    var $editordescription='Simple label field. Can execute a SQL statment. The result gets displayed.';

    protected function currency_format($number, $dec_point, $thousands_sep, $mindecimals)
    {
        $was_neg = $number < 0; // Because +0 == -0
        $number = abs($number);

        $tmp = explode('.', $number);
        
        if(isset($tmp[1]))
        {
            $tmp[1]=str_pad($tmp[1],2,'0');
        }
        else
            $tmp[1]="00";
        
        $out = number_format($tmp[0], 0, $dec_point, $thousands_sep);
        if (isset($tmp[1])) $out .= $dec_point.$tmp[1];

        if ($was_neg) $out = "-".$out;

        return $out;
    } 
    
    public function getInterpreterRender()
    {   
        $hsconfig=getHsConfig();
        $bezeichnung="";
        $sqlstring = $this->property['sqlstatment'];
        $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
        $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
        $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
        $sqlstring=$hsconfig->parseSQLString($sqlstring);
        // new feature, replace /*EXTRA()EXTRA*/ tags
        $sqlstring = $this->replaceExtraSql($sqlstring);

        $cssCheckbox="";
        $bezeichnung = $hsconfig->getScalar($sqlstring);
        if($this->property['format']=="currency")
        {
            $bezeichnung=$this->currency_format($bezeichnung, ".", ",", 2);
        }
        elseif($this->property['format']=="checkbox")
        {
            $cssCheckbox="text-align:center; line-height:20px; vertical-align:middle; font-size:20px; font-size:16px; color:#aaaaaa; ";
            if($bezeichnung=="1")
                $bezeichnung='&#9745;';
            else
                $bezeichnung='&#9744;';
        }

        $property = $this->property;
        $e = "<div data-customeridbox='{$this->getCustomerId()}' data-hasparentcontrol='{$this->getParentControl()}'
                class='$property[classname]' id='$this->id' style='
                {$this->getParentControlCss()}
                position:absolute;
                box-sizing:border-box;
                line-height:20px; 
                vertical-align:middle; 
                left:{$this->left}px;
                top:{$this->top}px;
                width:{$this->width}px;
                height:{$this->height}px;
                $cssCheckbox
                $property[css]
                $property[style]".
                ($property["invisible"]=='1'?" display:none; ":"").
                "'>$bezeichnung".($property["debugmode"] == '1' ? "<pre>$sqlstring</pre>" : "").
            "</div>";
        return $e;
    } 
 
    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textarea("SQL-Statment that get executed. Variables: #INDEX1#, #INDEX2#, #KENNZEICHEN1#",'sqlstatment');
        $html.=parent::getEditorProperty_Selectbox("Format",'format',array(''=>'auto','currency'=>'Currency', 'checkbox' => 'Checkbox (Only 0/1 values)'),'');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("CSS-style",'css');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>