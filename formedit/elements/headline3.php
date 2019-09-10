<?php
include_once('headline.php');

class headline3 extends headline
{
    var $name="headline3";
    var $editorname="Headline3";
    var $editordescription='Html-Tag h3';

    public function getInterpreterRender()
    {
        $e = parent::getInterpreterRender();
        $e=str_replace("<h1","<h3",$e);
        $e=str_replace("</h1>","</h3>",$e);
        return $e;
    }

}

?>