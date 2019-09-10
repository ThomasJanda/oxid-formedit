<?php

class chart_c3_line extends basecontrol
{
    var $name="chart_c3_line";

    var $editorname="Line (C3)";
    var $editorcategorie="Chart";
    var $editorshow=true;
    var $editordescription='Show a line chart based on a sql statment';

    public function interpreterBeforeRender()
    {
        $hsconfig=getHsConfig();
        $path=$hsconfig->getBaseUrl();
        $html="";
        $html.='<link href="'.$path.'/css/c3.css" media="screen" rel="stylesheet" type="text/css" />';
        $html.='<script type="text/javascript" src="'.$path.'/js/d3-4.13.0.min.js"></script>';
        $html.='<script type="text/javascript" src="'.$path.'/js/c3.min.js"></script>';
        return $html;
    }

    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();

        $datax=null;
        $datay=null;
        $sType = $this->property['type'];

        if($sType=="daily")
        {
            //x-axis
            $sSql = $this->property['sql_day_date_from'];
            $sSql=$hsconfig->parseSQLString($sSql);
            $sDateFrom = $hsconfig->getScalar($sSql);

            $sSql = $this->property['sql_day_date_to'];
            $sSql=$hsconfig->parseSQLString($sSql);
            $sDateTo = $hsconfig->getScalar($sSql);

            $iTimeFrom = strtotime($sDateFrom." 00:00:00");
            $iTimeTo = strtotime($sDateTo. " 23:59:59");
            $iDateDiff = $iTimeTo - $iTimeFrom;

            $iDayDuration = round($iDateDiff / (60 * 60 * 24));

            //echo $sDateFrom." ".$iDayDuration." ".$sDateTo;

            //values
            $datatmp=[];
            $sSql=$this->property['day_sql'];
            $sSql=str_replace("#DATESTART#",$sDateFrom, $sSql);
            $sSql=str_replace("#DATEEND#",$sDateTo, $sSql);
            $sSql=$hsconfig->parseSQLString($sSql);
            $rs = $hsconfig->execute($sSql);

            if($rs)
            {
                while($row = $rs->fetch_array(MYSQLI_NUM)) {
                    $datatmp[$row[0]] = $row[1];
                }
                $hsconfig->close($rs);
            }

            //data to axis
            for($x=0;$x<$iDayDuration;$x++)
            {
                //x
                $datax[$x]=date('Y-m-d',strtotime($sDateFrom) + ($x * 24 * 60 * 60));

                //y
                $v = 'null';
                if(isset($datatmp[$datax[$x]]))
                {
                    $v = $datatmp[$datax[$x]];
                }
                $datay[$x]=$v;

            }
        }
        if($sType=="weekly")
        {
            //x-axis
            $sSql = $this->property['sql_week_date_from'];
            $sSql=$hsconfig->parseSQLString($sSql);
            $sDateFrom = $hsconfig->getScalar($sSql);

            $sSql = $this->property['sql_week_date_to'];
            $sSql=$hsconfig->parseSQLString($sSql);
            $sDateTo = $hsconfig->getScalar($sSql);

            $iTimeFrom = strtotime($sDateFrom." 00:00:00");
            $iTimeTo = strtotime($sDateTo. " 23:59:59");
            $iDateDiff = $iTimeTo - $iTimeFrom;

            $iDayDuration = round($iDateDiff / (60 * 60 * 24));

            //echo $sDateFrom." ".$iDayDuration." ".$sDateTo;

            //values
            $datatmp=[];
            $sSql=$this->property['week_sql'];
            $sSql=str_replace("#DATESTART#",$sDateFrom, $sSql);
            $sSql=str_replace("#DATEEND#",$sDateTo, $sSql);
            $sSql=$hsconfig->parseSQLString($sSql);
            $rs = $hsconfig->execute($sSql);

            if($rs)
            {
                while($row = $rs->fetch_array(MYSQLI_NUM)) {
                    $datatmp[$row[0]] = $row[1];
                }
                $hsconfig->close($rs);
            }

            //data to axis
            $year="";
            $week="";
            $i=0;
            for($x=0;$x<$iDayDuration;$x++)
            {

                $tmp_year=date('o',strtotime($sDateFrom) + ($x * 24 * 60 * 60));
                $tmp_week=date('W',strtotime($sDateFrom) + ($x * 24 * 60 * 60));

                if($year!=$tmp_year || $week!=$tmp_week)
                {
                    $year = $tmp_year;
                    $week = $tmp_week;

                    //x
                    $tmp = $year."-".$week;
                    $datax[$i]=$tmp;

                    //y
                    $v = 'null';
                    if(isset($datatmp[$datax[$i]]))
                    {
                        $v = $datatmp[$datax[$i]];
                    }
                    $datay[$i]=$v;

                    $i++;
                }
            }
        }
        if($sType=="monthly")
        {
            //x-axis
            $sSql = $this->property['sql_month_date_from'];
            $sSql=$hsconfig->parseSQLString($sSql);
            $sDateFrom = $hsconfig->getScalar($sSql);

            $sSql = $this->property['sql_month_date_to'];
            $sSql=$hsconfig->parseSQLString($sSql);
            $sDateTo = $hsconfig->getScalar($sSql);

            $iTimeFrom = strtotime($sDateFrom." 00:00:00");
            $iTimeTo = strtotime($sDateTo. " 23:59:59");
            $iDateDiff = $iTimeTo - $iTimeFrom;

            $iDayDuration = round($iDateDiff / (60 * 60 * 24));

            //echo $sDateFrom." ".$iDayDuration." ".$sDateTo;

            //values
            $datatmp=[];
            $sSql=$this->property['month_sql'];
            $sSql=str_replace("#DATESTART#",$sDateFrom, $sSql);
            $sSql=str_replace("#DATEEND#",$sDateTo, $sSql);
            $sSql=$hsconfig->parseSQLString($sSql);
            $rs = $hsconfig->execute($sSql);

            if($rs)
            {
                while($row = $rs->fetch_array(MYSQLI_NUM)) {
                    $datatmp[$row[0]] = $row[1];
                }
                $hsconfig->close($rs);
            }

            //data to axis
            $year="";
            $month="";
            $i=0;
            for($x=0;$x<$iDayDuration;$x++)
            {

                $tmp_year=date('Y',strtotime($sDateFrom) + ($x * 24 * 60 * 60));
                $tmp_month=date('m',strtotime($sDateFrom) + ($x * 24 * 60 * 60));

                if($year!=$tmp_year || $month!=$tmp_month)
                {
                    $year = $tmp_year;
                    $month = $tmp_month;

                    //x
                    $tmp = $year."-".$month;
                    $datax[$i]=$tmp;

                    //y
                    $v = 'null';
                    if(isset($datatmp[$datax[$i]]))
                    {
                        $v = $datatmp[$datax[$i]];
                    }
                    $datay[$i]=$v;

                    $i++;
                }
            }
        }


        if($datax!=null && $datay!=null)
        {

            $e = '<div 
            data-customeridbox="' . $this->id . '" 
            data-hasparentcontrol="' . $this->getParentControl() . '" 
            class="' . $this->property['classname'] . '" 
            id="' . $this->id . '" 
            style="' . $this->getParentControlCss() . ' border:1px solid #cecece; overflow:hidden; ' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . ($this->property['fixwidth'] == "0" ? "calc(100% - " . ($this->left * 2) . "px)" : "{$this->width}px") . '; height:' . $this->height . 'px;  ' . ($this->property['invisible'] == "1" ? ' display:none; ' : '') . '">
            <div 
                id="chart_' . $this->id . '"
                data-customerid="' . $this->id . '" 
                name="' . $this->id . '" 
                style="width:100%; height:' . $this->height . 'px;  "
            ></div>
            <script>';

            $e .= "
var chart = c3.generate({
    bindto: '#chart_{$this->id}',
    data: {
        x: 'x',
        columns: [
            ['x', '" . implode("', '", $datax) . "'],
            ['data1', ".implode(', ', $datay)."]
        ],
        names: {
            data1: '".$this->property["title_x"]." (".$sDateFrom." - ".$sDateTo.")',
        }
    },
    axis: {
    ";
        if($sType=="daily") {
            $e .= "
            x: {
                type: 'timeseries',
                tick: {
                    format: '%Y-%m-%d'
                }
            },
            ";
        }
        if($sType=="weekly" || $sType=="monthly") {
            $e .= "
            x: {
                type: 'category'
            },
            ";
            }
        $e.="
        y: {
            label:
                {
                    text: '".$this->property["title_y"]."',
                    position: 'outer-middle'
                }
        }
    },
    line: {
        connectNull: true
    }
});
";

            $e .= '</script>
            </div>';
        }

        return $e;
    }

    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Textbox("Title-Y",'title_y');
        $html.=parent::getEditorProperty_Textbox("Title-X",'title_x');
        $html.=parent::getEditorProperty_Selectbox("Type",'type',array('daily'=>'Daily','weekly'=>'Weekly'),'daily');

        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Label("Type: Daily");
        $html.=parent::getEditorProperty_Textbox("SQL start date (YYYY-MM-DD) (Example: select date(now()) )",'sql_day_date_from');
        $html.=parent::getEditorProperty_Textbox("SQL end date (YYYY-MM-DD) (Example: select date(date_add_businessdays(now(),30)) )",'sql_day_date_to');
        $html.=parent::getEditorProperty_Textarea("SQL data (First column = Date (YYYY-MM-DD), Second column = Value, Variable: #DATESTART#, #DATEEND#)", "day_sql");

        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Label("Type: Weekly");
        $html.=parent::getEditorProperty_Textbox("SQL start date (YYYY-MM-DD) (Example: select date(now()) )",'sql_week_date_from');
        $html.=parent::getEditorProperty_Textbox("SQL end date (YYYY-MM-DD) (Example: select date(date_add_businessdays(now(),30)) )",'sql_week_date_to');
        $html.=parent::getEditorProperty_Textarea("SQL data (First column = Date (YYYY-WW), Second column = Value, Variable: #DATESTART#, #DATEEND#)", "week_sql");

        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Label("Type: Monthly");
        $html.=parent::getEditorProperty_Textbox("SQL start date (YYYY-MM-DD) (Example: select date(now()) )",'sql_month_date_from');
        $html.=parent::getEditorProperty_Textbox("SQL end date (YYYY-MM-DD) (Example: select date(date_add_businessdays(now(),30)) )",'sql_month_date_to');
        $html.=parent::getEditorProperty_Textarea("SQL data (First column = Date (YYYY-MM), Second column = Value, Variable: #DATESTART#, #DATEEND#)", "month_sql");


        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}