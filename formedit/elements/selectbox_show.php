<?php

class selectbox_show extends basecontrol
{
    var $name = "selectbox_show";

    var $editorname = "Selectbox Show";
    var $editorcategorie = "Database Items";
    var $editorshow = true;
    var $editordescription = 'Can handle the MySql show command';


    public function getInterpreterRender()
    {
        $value = parent::getInterpreterRequestValue();
        $hsconfig = getHsConfig();

        $e = "";
        if ($this->property['readonly'] == "1")
        {
            $e .= '<input data-customerid="' . $this->getCustomerId() . '" type="hidden" name="' . $this->id . '" value="' . $value . '">';
        }
        $e .= '<div data-customeridbox="' . $this->getCustomerId() . '" data-hasparentcontrol="' . $this->getParentControl() . '" class="' . $this->property['classname'] . '" id="' . $this->id . '" style="' . $this->getParentControlCss() . '' . $this->property['css'] . ' position:absolute; left:' . $this->left . 'px; top:' . $this->top . 'px; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; '.($this->property['invisible']=="1"?' display:none; ':'').'">
            <select 
                ';
        if ($this->property['readonly'] != "1")
        {
            $e .= 'data-customerid="' . $this->getCustomerId() . '" ';
        }
        if ($value) {
            $e .= 'data-original="' . $value . '" ';
        }
        $e .= '
                id="' . $this->id . 'selectbox"
                name="' . $this->id . '" 
                style="vertical-align:middle; width:' . $this->width . 'px; height:' . $this->height . 'px; line-height:' . $this->height . 'px; border:1px solid #dddddd; ' . (array_key_exists($this->id, $this->ainterpretererrorlist) ? 'border-color:red; ' : '') . ' "
                tabindex="' . $this->property['taborder'] . '"
                ' . ($this->property['readonly'] == "1" ? 'readonly="readonly" disabled="disabled"' : "") . '
            >
            ';

        $values=[];
        if($this->property['type']=="show tables")
        {
            /**
             * @var mysqli_result $rs
             */
            $sql = "show tables";
            if($rs = $hsconfig->execute($sql))
            {
                while($row = $rs->fetch_array(MYSQLI_NUM))
                {
                    $values[$row[0]]=$row[0];
                }
                $hsconfig->close($rs);
            }
        }
        elseif($this->property['type']=="show columns")
        {
            $sql = $this->property['sql_type_show_columns'];
            $sql = $hsconfig->parseSQLString($sql);
            $sTable = $hsconfig->getScalar($sql);

            $sql = "show columns from `".$sTable."`";
            if($rs = $hsconfig->execute($sql))
            {
                while($row = $rs->fetch_array(MYSQLI_NUM))
                {
                    $values[$row[0]]=$row[0];
                }
                $hsconfig->close($rs);
            }
        }

        if($this->property['addempty']=="1")
        {
            $e .= "<option value=''></option>";
        }

        foreach ($values as $k=>$v)
        {
            $e .= "<option value='" . $k . "' " . ($k == $value ? 'selected' : '') . ">" . $v . "</option>";
        }

        $e .= '</select></div>';


        $setreadonly = trim($this->property['setreadonly']);
        if ($setreadonly != "")
        {
            $interpreterid = $hsconfig->getInterpreterId();
            $setreadonly = explode("\n", str_replace("\r", "", $setreadonly));
            $all = array();
            $readonly = array();
            foreach($setreadonly as $line)
            {
                $tmp = explode("|", $line);
                $v = $tmp[0];
                $cids = explode(":", $tmp[1]);

                $readonly[$v] = $cids;
                $all = array_merge($all, $cids);
            }
            $all = array_unique($all);


            $e .= '<script type="text/javascript">
            function setReadOnly' . $interpreterid . $this->name . $this->id . '()
            {
                var v = $("#' . $this->id . 'selectbox").val();
                ';
            foreach ($all as $customeridbox)
            {
                //enable
                $e .= '$( "#formular div[data-customeridbox=' . $customeridbox . '] .enableelement_' . $interpreterid . $this->name . $this->id . '" ).css("display","none"); ';
            }
            foreach($readonly as $v=>$cids)
            {
                $e.='if(v=="'.$v.'") {';
                foreach($cids as $customeridbox)
                {
                    $e.='$( "#formular div[data-customeridbox='.$customeridbox.'] .enableelement_'.$interpreterid.$this->name.$this->id.'" ).css("display","block"); ';
                    $e.='$( "#formular div[data-customeridbox='.$customeridbox.'] input[type=checkbox]" ).prop("checked", false); ';
                }
                $e.='}';
            }
            $e.='}
            
            function init_elements_'.$interpreterid.$this->name.$this->id.'()
            {
                ';
            foreach($all as $customeridbox)
            {
                $e.='$( \'<div class="enableelement_clipboard enableelement_'.$interpreterid.$this->name.$this->id.'" style="position:absolute; left:0px; right:0px; top:0px; bottom:-5px; background-color:white; opacity:0.5; z-index:10000; display:none; "></div>\' ).appendTo( $( "#formular div[data-customeridbox='.$customeridbox.']" ) ); ';
            }
            $e.='
            }
            
            $(function() {
                init_elements_'.$interpreterid.$this->name.$this->id.'();
                $("#'.$this->id.'selectbox").change(function() {
                    setReadOnly'.$interpreterid.$this->name.$this->id.'();
                });
                setReadOnly'.$interpreterid.$this->name.$this->id.'();
            });
            
            </script>';
        }

        return $e;
    }


    public function getEditorProperty()
    {
        $html='';
        $html.=parent::getEditorPropertyHeader();
        $html.=parent::getEditorProperty_Selectbox("Kind of values",'type',array('show tables'=>'show tables','show columns'=>'show columns'),'show tables');
        $html.=parent::getEditorProperty_Textarea("Sql that return the table name for the 'show columns' type",'sql_type_show_columns');
        $html.=parent::getEditorProperty_Textbox("Value that gets selected at the beginning",'standardvalue');
        $html.=parent::getEditorProperty_Checkbox("Add empty value",'addempty');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Checkbox("Readonly",'readonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorProperty_Textarea("Set readonly, if a value is selected. Schema: value|customerid:customerid... One Value with the Customer Ids per line.",'setreadonly');
        $html.=parent::getEditorProperty_Line();
        $html.=parent::getEditorPropertyFooter();
        return $html;
    }
}

?>
