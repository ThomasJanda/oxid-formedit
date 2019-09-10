<?php

class chart extends basecontrol
{
    var $name="chart";

    var $editorname="Chart (google)";
    var $editorcategorie="Chart";
    var $editorshow=true;
    var $editordescription='Show a line chart based on a sql statment';
    
    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
        
        $e='';
        global $basecontrol_chart_init;
        if($basecontrol_chart_init!="1")
        {
            $basecontrol_chart_init="1";
            
            $e='<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">
            google.charts.load(\'current\', {packages: [\'corechart\']});
            </script>';
        }
        
        $sqlstring = $this->property['sqlstatment'];
        $sqlstring=str_replace('#INDEX1#',$hsconfig->getIndex1Value(),$sqlstring);
        $sqlstring=str_replace('#INDEX2#',$hsconfig->getIndex2Value(),$sqlstring);
        $sqlstring=str_replace('#KENNZEICHEN1#',$hsconfig->getKennzeichen1Value(),$sqlstring);
        $sqlstring=$hsconfig->parseSQLString($sqlstring);
        
        $datatable="";
        $datacolumns="";

        $rs = $hsconfig->execute($sqlstring);
        //$rs=mysql_query($sqlstring,$hsconfig->getDbId());
        if($rs)
        {
            $fields = $rs->fetch_fields();
            $first = true;
            foreach($fields as $field)
            {
                $type='number';
                if($first)
                {
                    $type='date';
                    $first=false;
                }
                $datacolumns.="\n"."data.addColumn('".$type."', '".trim($field->name)."'); ";
            }
            //$columntypes=explode("|",$this->property['columntypes']);
            /*
            for($x=0;$x<mysql_num_fields($rs);$x++)
            {
                //data.addColumn('number', 'Importe name');
                $datacolumns.="\n"."data.addColumn('".($x==0?'date':'number')."', '".trim(mysql_field_name($rs,$x))."'); ";
            }
            */
            while($row = $rs->fetch_assoc())
            {
                $datarow="";
                $first = true;
                foreach($fields as $field)
                {
                    $value = $row[$field->name];
                    $type='number';
                    if($first)
                    {
                        $type='date';
                        $first=false;
                    }
                    if($type=="date")
                    {
                        $d=explode("-",$value);
                        if(!isset($d[1]))
                            $d[1]=1;
                        if(!isset($d[2]))
                            $d[2]=1;
                        $value = "new Date(".$d[0].",".($d[1]-1).",".$d[2].")";
                    }
                    if($datarow!="")
                        $datarow.=",";
                    $datarow.=$value;
                }
                $datarow="[".$datarow."]";

                if($datatable!="")
                    $datatable.=",";
                $datatable.="\n".$datarow;
            }
            /*
            for($xx=0;$xx<mysql_num_rows($rs);$xx++)
            {
                $datarow="";
                for($x=0;$x<mysql_num_fields($rs);$x++)
                {
                    $value=mysql_result($rs,$xx,$x);
                    $type = ($x==0?'date':'number');
                    if($type=="date")
                    {
                        $d=explode("-",$value);
                        if(!isset($d[1]))
                            $d[1]=1;
                        if(!isset($d[2]))
                            $d[2]=1;
                        $value = "new Date(".$d[0].",".($d[1]-1).",".$d[2].")";
                    }
                    if($datarow!="")
                        $datarow.=",";
                    $datarow.=$value;
                }
                $datarow="[".$datarow."]";
                
                if($datatable!="")
                    $datatable.=",";
                $datatable.="\n".$datarow;
            }
            */
            $hsconfig->close($rs);
        }
        
        $e.='<div data-customeridbox="'.$this->getCustomerId().'" data-hasparentcontrol="'.$this->getParentControl().'" class="'.$this->property['classname'].'" id="'.$this->id.'" style="border:1px solid lightgray; '.$this->getParentControlCss().''.$this->property['css'].' position:absolute; left:'.$this->left.'px; top:'.$this->top.'px; width:'.$this->width.'px; height:'.$this->height.'px; line-height:'.$this->height.'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">';
        if($datatable!="")
        {
            $e.='<script type="text/javascript">

                  google.setOnLoadCallback(drawchart'.$this->id.');

                  function drawchart'.$this->id.'() 
                  {
                    var data = new google.visualization.DataTable();
                    '.$datacolumns.'
                    data.addRows([
                    '.$datatable.'
                    ]);

                    var options = {"title":"'.$this->property['title'].'",
                                   "width":'.$this->width.',
                                   "height":'.$this->height.',
                                   "pointSize":3};

                    var chart = new google.visualization.LineChart(document.getElementById("chart'.$this->id.'"));
                    chart.draw(data, options);
                  }
            </script>
            <div id="chart'.$this->id.'"></div>';
        }
        else
        {
            $e.="<div style='text-align:center; line-height:20px; '><b>".$this->property['title']."</b><br>No data to display</div>";
        }
        
        $e.=($this->property['debugmode']=="1"?'<div>'.$sqlstring.'</div>':'').'</div>';
        return $e;
    }
    
    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'title');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("SQL-Statment that get executed. (Variables: #INDEX1#, #INDEX2#, #KENNZEICHEN1#). First column has to be a date, all other columns are lines in the chart (numberic)",'sqlstatment');
        //$html.=parent::getEditorProperty_Textarea("Column types for the chart in the same order as the sqlstatment seperated with |. Column types are date, number",'columntypes');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("CSS-style",'css');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Debug-modus",'debugmode','0');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}
