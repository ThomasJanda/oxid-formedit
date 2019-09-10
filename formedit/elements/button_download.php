<?php

class button_download extends basecontrol
{
    var $name="button_download";

    var $editorname="Download";
    var $editorcategorie="Button";
    var $editorshow=true;
    var $editordescription='Button of a csv-download';

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
        $interpreterid = $hsconfig->getInterpreterId();
        
        return '<input type="hidden" id="'.__CLASS__.$this->id.'" name="'.$this->id.'" value="0">
        <button 
        type="button" 
        data-hasparentcontrol="'.$this->getParentControl().'" 
         data-customeridbox="'.$this->getCustomerId().'"
        id="'.$this->id.'" 
        tabindex="'.$this->property['taborder'].'"
        class="'.$this->property['classname'].'" 
        style="'.$this->getParentControlCss().' '.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'"
        onclick="
            $(\'#'.__CLASS__.$this->id.'\').val(\'1\');
            exportcsv'.$interpreterid.__CLASS__.$this->id.'();
        ">'.$this->property['bezeichnung'].'</button>        
        <script type="text/javascript"> 
            $("#'.$this->id.'").button();
            
    		function exportcsv'.$interpreterid.__CLASS__.$this->id.'()
    		{
    			var param="project='.$hsconfig->getProjectName().'&elementclass='.$interpreterid.__CLASS__.'";
    			param+="&elementid='.$this->id.'";
    			param+="&elementfunction=getExportTable";
    			param+="&'.$hsconfig->getInterpreterParameterGet().'";

                window.open("'.$hsconfig->getBaseUrl().'/interpreter_ajax.php?" + param);
                
    		}
        </script>';
    }

    public function getEditorRender($text = "")
    {
        return parent::getEditorRender($this->property['bezeichnung']);
    }


 
    public function tocsv($text)
    {
      $text=trim($text);
      $text=str_replace('"','""',$text);
      $text='"'.$text.'"';
      return $text;
    }
    public function getExportTable()
    {
        $filename="Export.csv";
        $application="text/csv";
        header( "Content-Type: $application" ); 
        header( "Content-Disposition: attachment; filename=$filename"); 
        header( "Content-Description: csv File" ); 
        header( "Pragma: no-cache" ); 
        header( "Expires: 0" ); 



        $hsconfig=getHsConfig();
        
        $sqlstring=$this->property['sqlstring'];
        $sqlstring=$hsconfig->parseSQLString($sqlstring);
		$rs=$hsconfig->execute($sqlstring);
        if($rs)
        {
            $fields = $rs->fetch_fields();
            $x=0;
            foreach($fields as $field)
            {
                if($x!=0) echo ';';
                echo $this->tocsv($field->name);
                $x++;
            }

            echo "\r\n";

            while($row = $rs->fetch_array(MYSQLI_NUM))
            {
                $xx=0;
                foreach($fields as $field)
                {
                    if($xx!=0) echo ";";
                    echo $this->tocsv($row[$xx]);
                    $xx++;
                }
                echo "\r\n";
            }
        }
        else
        {
            echo "Sql request not valid";
        }

    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'bezeichnung');
        $html.=parent::getEditorProperty_Line();
		$html.=parent::getEditorProperty_Textarea("SQL-statment, that gets exported as a csv-file",'sqlstring');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }

}

?>
