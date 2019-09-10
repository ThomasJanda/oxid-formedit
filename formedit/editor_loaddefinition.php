<?php
$id=$_REQUEST["containerid"];
if($id=="")
    die("");
$table=$_REQUEST['table'];
if($table=="")
    die("");

require_once __DIR__ . "/core/hsinit.php";
require_once __DIR__ . "/core/hsiniteditor.php";
$hsconfig=getHsConfig();

//$e = unserialize($_SESSION['editor'][$id]['property']);

$sql="describe ".$table;
$rs = $hsconfig->execute($sql);
if($rs)
{

    $top=20;
    while($row = $rs->fetch_assoc())
    {

        $name = $row['Field'];
        $type = $row['Type'];
        //echo $name." - ".$type.PHP_EOL;
        $addLabel = true;
        $classname = "";
        if ($type == "tinyint(1)") {
            //checkbox
            $addLabel = false;
            $classname="checkbox";
        } elseif ($type == "double") {
            $classname="textbox_double";
        } elseif ($type == "text" || $type == "longtext") {
            $classname="htmlbox2";
        } elseif ($type == "timestamp" || $type == "datetime") {
            $classname="datetimebox";
        } elseif ($type == "date") {
            $classname="datebox2";
        } elseif (substr($type, 0, 4) == "int(") {
            $classname="textbox_integer";
        } else {
            $classname="textbox";
        }


        if($classname!="")
        {
            $fileName = strtolower($classname);
            $basePath = rtrim($hsconfig->getBasePath, "/");
            require_once "$basePath/elements/$fileName.php";

            $left=220;
            $e=new $classname();
            $e->left=$left;
            $e->top=$top;
            $e->containerid=$id;
            if($classname=="checkbox")
                $e->property['bezeichnung']=$name;
            $e->property['datenbankspalte']=$name;
            $e->property['customerid']=$name;

            echo $e->getEditorRender();
            echo "\n--||||--\n";
            $_SESSION['editor'][$id]['elements'][$e->id]=serialize($e);
        }
        if($addLabel)
        {
            $classname="label";
            include_once($hsconfig->getBasePath."elements/".$classname.".php");

            $left=10;
            $e=new $classname();
            $e->left=$left;
            $e->top=$top;
            $e->property['bezeichnung']=$name;
            $e->containerid=$id;
            echo $e->getEditorRender();
            echo "\n--||||--\n";
            $_SESSION['editor'][$id]['elements'][$e->id]=serialize($e);
        }

        $top+=30;

    }
}
