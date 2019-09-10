<?php

$hsconfig=getHsConfig();
$d=opendir($hsconfig->getBasePath."elements");

$categorie=array();

$files=array();
while ($file = readdir ($d))
{
    if(is_file($hsconfig->getBasePath."elements/".$file) && substr(strtolower($file),strlen($file)-4)==".php")
    {
        $files[]=$file;
    }
}
closedir($d);
sort($files);

for($x=0;$x<count($files);$x++)
{
    $file=$files[$x];
    
    include_once($hsconfig->getBasePath."elements/".$file);
    $classname=substr($file,0,strlen($file)-4);
    $e=new $classname();
    if($e->editorshow)
    {
        $item['filename']=$file;
        $item['classname']=get_class($e);
        $item['editorname']=$e->editorname;
        $categorie[$e->editorcategorie][]=$item;
    }
    unset($e);
}
/*
while ($file = readdir ($d))
{
    if(is_file($hsconfig->getBasePath."elements/".$file))
    {
        include_once($hsconfig->getBasePath."elements/".$file);
        $classname=substr($file,0,strlen($file)-4);
        $e=new $classname();
        if($e->editorshow)
        {
            $item['filename']=$file;
            $item['classname']=get_class($e);
            $item['editorname']=$e->editorname;
            $categorie[$e->editorcategorie][]=$item;
        }
        unset($e);
    }
}
closedir($d);
*/

foreach($categorie as $categoriename=>$elements)
{
    echo '<h3><a href="#">'.$categoriename.'</a></h3>
	<div style="padding:0; ">';
        foreach($elements as $element)
        {
            echo '<div class="control ui-widget-content">
            <input type="hidden" name="classname" value="'.$element['classname'].'">
            <span>'.$element['editorname'].'</span>
            </div>';
        }
	echo '</div>';

}
?>