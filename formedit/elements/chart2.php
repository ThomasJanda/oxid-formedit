<?php

class chart2 extends basecontrol
{
    var $name="chart2";

    var $editorname="Chart2 (Google)";
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

        $first=true;
        $datatable="";
        $datacolumns="";

        $days = $this->property['days'];
        if($days=="" || !is_numeric($days))
            $days=0;

        for($xx=$days ;$xx>=0; $xx--)
        {
            $date = date('Y-m-d',time() - ( (26*60*60) * $xx));
            $sql=str_replace('#DATE#',$date,$sqlstring);

            $rs = $hsconfig->execute($sql);
            if($rs)
            {
                $datarow="";
                $allZero=true;

                $firstrow = true;
                while($row = $rs->fetch_array(MYSQLI_NUM))
                {
                    if($firstrow)
                    {
                        $firstrow=false;
                        if($first)
                        {
                            $datacolumns.="\n"."data.addColumn('date', 'date'); ";
                        }

                        $d=explode("-",$date);
                        if(!isset($d[1]))
                            $d[1]=1;
                        if(!isset($d[2]))
                            $d[2]=1;
                        $value = "new Date(".($d[0]*1).",".($d[1]-1).",".($d[2]*1).")";
                        $datarow=$value;
                    }

                    if($first)
                    {
                        $value = $row[0];
                        $datacolumns.="\n"."data.addColumn('number', '".trim($value)."'); ";
                    }

                    $value=trim($row[1]);
                    if($value=="" || !is_numeric($value))
                        $value="0";

                    if($value!="0")
                        $allZero=false;

                    $datarow.=",".$value;
                }
                if(!$allZero) {
                    $datarow = "[" . $datarow . "]";

                    if ($datatable != "") {
                        $datatable .= ",";
                    }
                    $datatable .= "\n" . $datarow;
                }
                $first=false;

                $hsconfig->close($rs);
            }
            /*
            $rs=mysql_query($sql,$hsconfig->getDbId());
            if($rs)
            {
                $datarow="";
                $allZero=true;
                for($x=0;$x<mysql_num_rows($rs);$x++)
                {
                    if($x==0)
                    {
                        if($first)
                        {
                            $datacolumns.="\n"."data.addColumn('date', 'date'); ";
                        }

                        $d=explode("-",$date);
                        if(!isset($d[1]))
                            $d[1]=1;
                        if(!isset($d[2]))
                            $d[2]=1;
                        $value = "new Date(".($d[0]*1).",".($d[1]-1).",".($d[2]*1).")";
                        $datarow=$value;
                    }

                    if($first)
                    {
                        $datacolumns.="\n"."data.addColumn('number', '".trim(mysql_result($rs,$x,0))."'); ";
                    }

                    $value=trim(mysql_result($rs,$x,1));
                    if($value=="" || !is_numeric($value))
                        $value="0";

                    if($value!="0")
                        $allZero=false;

                    $datarow.=",".$value;
                }
                if(!$allZero) {
                    $datarow = "[" . $datarow . "]";

                    if ($datatable != "") {
                        $datatable .= ",";
                    }
                    $datatable .= "\n" . $datarow;
                }
                $first=false;
            }
            */
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

        if($this->property['debugmode']=="1")
        {
            $e.='<div style="line-height:12px; ">
            Dates:<br>';
            for($xxx=$days ;$xxx>=0; $xxx--) {
                $date = date('Y-m-d',time() - ( (26*60*60) * $xxx));
                $e.=$date." ";
            }
            $e.='<br><br>';
            $e.='Sql:<br>'.$sqlstring.'</div>';
        }
        $e.='</div>';

        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title",'title');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textbox("How many days",'days','180');
        $html.=parent::getEditorProperty_Textarea("SQL-Statment: (Variable #DATE#) Can return many rows. Each row represent a point at the date it is ask for. The order of the row has always to be the same, otherwise it can not draw the line correct. The sql statment have to return 2 columns. The first is the name of the line, the second the value (number).",'sqlstatment');
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
