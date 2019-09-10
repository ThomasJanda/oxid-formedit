<?php
include_once('headline.php');

class headline2 extends headline
{
    var $name="headline2";
    var $editorname="Headline2";
    var $editordescription='Html-Tag h2';

    public function getInterpreterRender()
    {
        $e = parent::getInterpreterRender();
        $e=str_replace("<h1","<h2",$e);
        $e=str_replace("</h1>","</h2>",$e);
        return $e;
    }

}

?>