<?php

class hsproperty implements arrayaccess {

    private $container = array();

    public function __construct() {
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        $v = isset($this->container[$offset]) ? $this->container[$offset] : null;
        if($v!==null)
        {
            if($v!="" && strlen($v)>5)
            {
                $hsconfig = getHsConfig();
                if(!$hsconfig->getEditorMode())
                {
                    $sTextString = 'SQL::';
                    //echo substr(strtoupper(trim($v)),0,strlen($sTextString))."<br>";
                    if(substr(strtoupper(trim($v)),0,strlen($sTextString)) == $sTextString)
                    {
                        $sSql = trim(substr((trim($v)),strlen($sTextString)));
                        $sSql=$hsconfig->parseSQLString($sSql);
                        $v = $hsconfig->getScalar($sSql);
                    }

                    $sTextString = 'PHP::';
                    //echo substr(strtoupper(trim($v)),0,strlen($sTextString))."<br>";
                    if(substr(strtoupper(trim($v)),0,strlen($sTextString)) == $sTextString)
                    {
                        $sPhp = trim(substr((trim($v)),strlen($sTextString)));
                        ob_start();
                        @eval($sPhp);
                        $v = ob_get_contents();
                        ob_end_clean();
                    }
                }
            }

        }
        return $v;
    }


    public function getRawArray()
    {
        return $this->container;
    }
    public function setRawArray($aValues)
    {
        $this->container = $aValues;
    }

    public function __toString() {
        return (string)print_r($this, true);
    }
}
