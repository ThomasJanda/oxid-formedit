<?php

class chart_c3_pie extends basecontrol
{
    var $name="chart_c3_pie";

    var $editorname="Pie (C3)";
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

        $data=null;

        //values
        $datatmp=[];
        $sSql=$this->property['pie_sql'];
        $sSql=$hsconfig->parseSQLString($sSql);
        $rs = $hsconfig->execute($sSql);

        if($rs)
        {
            while($row = $rs->fetch_array(MYSQLI_NUM)) {
                $data[]="['".$row[0]."', ".$row[1]."]";
            }
            $hsconfig->close($rs);
        }




        if($data!=null)
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
        columns: [
            ".implode(",",$data)."
        ],
        type: 'pie',
        onclick: function (d, i) { console.log(\"onclick\", d, i); },
        onmouseover: function (d, i) { console.log(\"onmouseover\", d, i); },
        onmouseout: function (d, i) { console.log(\"onmouseout\", d, i); }
    },
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
        $html.=parent::getEditorProperty_Textarea("SQL data (First column = Title, Second column = Value)", "pie_sql");
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Fix width from the element, otherwise 100% - 2 times left", 'fixwidth', '1');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter(true,false,false);
        return $html;
    }
}