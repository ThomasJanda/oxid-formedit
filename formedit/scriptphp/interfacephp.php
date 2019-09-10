<?php

class interfacephp
{

    protected static $_logPath   = __DIR__ . "/../../../../";
    public           $aoelements = [];


    public function __construct()
    {
        $hsconfig=getHsConfig();
    }

    /**
     * Return the BaseControl which has the customer id $customerId
     *
     * @param string $customerId
     *
     * @return boolean|basecontrol
     */
    public function getOldElementByCustomerId($customerId)
    {
        $oldoelements = $this->interpreterGetElements();
        /** @var basecontrol $oElement */
        foreach ($oldoelements as &$oElement) {
            if ($oElement->getCustomerId() == $customerId) {
                return $oElement;
            }
        }

        return false;
    }

    public function getRequestByDbField($dbField)
    {
        $oldoelements = $this->interpreterGetElements();

        /** @var basecontrol $oElement */
        foreach ($oldoelements as $oElement) {
            if ($oElement->property["datenbankspalte"] == $dbField) {
                if (isset($_REQUEST[$oElement->id])) {
                    return $_REQUEST[$oElement->id];
                }
            }
        }

        return false;
    }

    public function interpreterInit()
    {
        $hsconfig = getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }

    /**
     * @return basecontrol[]|null
     */
    public function interpreterGetElements()
    {
        $hsconfig = getHsConfig();
        return $hsconfig->getElements();
    }

    public function getInterpreterId()
    {
        $hsconfig = getHsConfig();
        return $hsconfig->getInterpreterId();
    }

    public function interpreterBeforeDelete()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterDelete($table, $colindex, $indexvalue)
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterAfterDelete()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }


    public function interpreterBeforeBulkDelete()
    {
        $hsconfig = getHsConfig();
        $returnvalue = '';

        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterBulkDelete($table, $colindex, $indexvalue)
    {
        $hsconfig = getHsConfig();
        $returnvalue = '';

        //echo __FUNCTION__;
        return $returnvalue;
    }

    public function interpreterAfterBulkDelete()
    {
        $hsconfig = getHsConfig();
        $returnvalue = '';

        //echo __FUNCTION__;
        return $returnvalue;
    }



    public function setInterpreterIsFirstNew()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function setInterpreterIsNew()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterBeforeProveNew()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterProveNew($table, $colindex, $indexvalue)
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterAfterProveNew()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterBeforeSaveNew()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterSaveNew($table, $colindex, $indexvalue)
    {
        $hsconfig=getHsConfig();
        $returnvalue=false;
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterAfterSaveNew()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    
 
 
    public function setInterpreterIsFirstEdit()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function setInterpreterIsEdit()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterBeforeProveEdit()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterProveEdit($table, $colindex, $indexvalue)
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterAfterProveEdit()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterBeforeSaveEdit()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterSaveEdit($table, $colindex, $indexvalue)
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function interpreterAfterSaveEdit()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    
    
    
    public function interpreterBeforeRender()
    {
        
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    public function getInterpreterRender()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
        
    }
    public function interpreterAfterRender()
    {
        $hsconfig=getHsConfig();
        $returnvalue="";
        //echo __FUNCTION__;
        return $returnvalue;
    }
    
    
    
    public function interpreterFinish()
    {
        $returnvalue="";
        return $returnvalue;
    }

    /**
     * Simplifies the process of getting a start parameter from formedit iframes.
     * @param string $p  start parameter string name
     * @return string  value of the start parameter on this request
     */
    protected function getStartParam($p)
    {
        if ($startParam = hsconfig::getInstance()->getInterpreterValue("startparam")) {
            if (isset($startParam[$p])) {
                return $startParam[$p];
            }
        }
        return null;
    }


    /**
     * @param string $customerID Customer or Custom ID that has the element which we want the value.
     *
     * @param null   $defaultValue
     * @param bool   $is_array
     *
     * @return string|array
     */
    public function getInterpreterRequestValueByCustomerId($customerID, $defaultValue = null, $is_array = false)
    {
        $oldElement = $this->getOldElementByCustomerId($customerID);


        if ($oldElement) {
            if ($is_array) {
                return $oldElement->getInterpreterRequestValues();
            } else {
                return $oldElement->getInterpreterRequestValue();
            }
        }

        return $defaultValue;
    }

    public function setInterpreterRequestValueByCustomerId($customerID, $value)
    {
        $oldElement = $this->getOldElementByCustomerId($customerID);
        if ($oldElement) {
            $oldElement->setInterpreterRequestValue($value);
        }
    }

    public function getConfig()
    {
        return getHsConfig();
    }
}
