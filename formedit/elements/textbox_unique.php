<?php
include_once(__DIR__.'/textbox.php');
class textbox_unique extends textbox
{
    var $name="textbox_unique";

    var $editorname="Textbox Unique";
    var $editordescription='HTML textbox. Test if the value is unique in the column of the table.';


    public function interpreterProve($table, $colindex, $indexvalue)
    {
        //If is not readonly and passed the parent validations.
        if ($this->property['readonly'] != "1" && parent::interpreterProve($table, $colindex, $indexvalue) == false) {
            $value = $this->getInterpreterRequestValue();

            if ($value === '') {
                return false;
            }

            //If is not an allowed value and the min. length is defined as numeric bigger than 0
            $minlength = $this->property['minlength'];
            if (is_numeric($minlength) && $minlength > 0) {
                //Validate allowed strings
                $ignorelength = $this->property['ignorelength'];

                //Explode the ignore values and convert all of them to uppercase.
                $ignorelength_values = explode('|', $ignorelength);
                $ignorelength_values = array_map(function ($ignore_value) {
                    return strtoupper($ignore_value);
                }, $ignorelength_values);

                $allowed = false;
                if (in_array(strtoupper($value), $ignorelength_values)) {
                    $allowed = true;
                }

                $length = strlen($value);
                if (!$allowed && $length < $minlength) {
                    return [$this->id => "'$value' should have at least ".$minlength." chars."];
                }
            }


            $hsconfig=getHsConfig();
            $col=$this->property['datenbankspalte'];
            $sql="select 
              count(*) 
            from `".$table."` 
            where `".$colindex."`<>'".$hsconfig->escapeString($indexvalue)."'
            and `".$col."`='".$hsconfig->escapeString($value)."'";
            if($hsconfig->getScalar($sql)!="0")
            {
                return [$this->id => "'$value' isn´t unique"];
            }

        }

        return false;
    }

}

?>