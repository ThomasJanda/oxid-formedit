<?php
/*
 * with this class you can overload the sqlparser.
 * every sql statment, which is use in the forms/elements will send throught this class and can modify.
 * for example it will replace #INDEX1#, #INDEX2#, #KENNZEICHEN1#, #SESSION...# replaced.
 *
 * you an overload it and add your own tags if nessesary
*/
class interfacesqlparser
{
    public function __construct()
    {
        $hsconfig=getHsConfig();
    }

    /**
     * @param $sqlstring : example: "select * from oxorder where index1='#INDEX1#'";
     *
     * @return mixed
     */
    public function parseSQLString_before($sqlstring, $param=array())
    {
        $hsconfig=getHsConfig();
        return $sqlstring;
    }

    /**
     * @param $sqlstring : example: "select * from oxorder where index1='2342322'";
     *
     * @return mixed
     */
    public function parseSQLString_after($sqlstring, $param=array())
    {
        // here the extra stuff
        $hsconfig=getHsConfig();
        return $sqlstring;
    } 
   
}

?>