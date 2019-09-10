<?php 

class interfacevalidation
{
    
    /*
    before a form load
    
    params:
    formularid = unique id from the loaded formular
    values = array with uniquid from the element and the value e.g. array('element1' => 'value1', 'element2' => 'value2')
    action = NEW, EDIT
    table = current table that the formular is connected
    indexcolname = current index column from the form
    index = current index from the datarow
    */
    public function init($formularid, $values, $action, $tablename, $indexcolname, $index)
    {
        //usefull, if you want create a hash with all values from the form or the database
        //if you need access to the current tab/elements use
        //global $otab;
        //global $oelements;
        $hsconfig=getHsConfig();
        
    }
    
    /*
    before making a action 
    
    params:
    formularid = unique id from the loaded formular
    values = array with uniquid from the element and the value e.g. array('element1' => 'value1', 'element2' => 'value2')
    action = NEW, EDIT, DELETE, DELETEKENNZEICHEN1, NEW_SAVE, EDIT_SAVE
    table = current table that the formular is connected
    indexcolname = current index column from the form
    index = current index from the datarow
    
    return:
    a message that should display. if no text returnd, no popup appear
    */
    public function validate($formularid, $values, $action, $tablename, $indexcolname, $index)
    {
        //now you can prove the values if all is ok
        //if you need access to the current tab/elements use
        //global $otab;
        //global $oelements;
        
        //example if you like a popup
        //return "Please type your 'Masterpassword'";
        //if you need no password validation
        //return "";
        
        $hsconfig=getHsConfig();
        return "";
    }
    
    /*
    approve password. if password is valid
    
    formularid = unique id from the loaded formular
    values = array with uniquid from the element and the value e.g. array('element1' => 'value1', 'element2' => 'value2')
    action = NEW, EDIT, DELETE, DELETEKENNZEICHEN1, NEW_SAVE, EDIT_SAVE
    table = current table that the formular is connected
    indexcolname = current index column from the form
    index = current index from the datarow
    password = text that the user type in
    
    return:
    true if valid, false if not
    */
    public function passwordapproval($formularid, $values, $action, $tablename, $indexcolname, $index, $password)
    {
        //prove, if password is valid
        //if you need access to the current tab/elements use
        //global $otab;
        //global $oelements;
        
        $hsconfig=getHsConfig();
        return false;
    }
}