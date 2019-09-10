<?php
require_once __DIR__ . "/core/hsinit.php";
require_once __DIR__ . "/core/hsiniteditor.php";
require_once __DIR__ . "/inc/cpffileparser.php";
$config = getHsConfig();

if (isset($_REQUEST["projectsave"]) && $_REQUEST["projectsave"] != "") {
    //rename filename, if nessesary
    $path = $_REQUEST["projectsave"];
    $filename = basename($path);
    $dirPath = dirname($path);

    //make a backup
    if (file_exists($path)) { // see ./config.inc.php
        if( !is_dir($dirPath . "/backup") ) {
            if (!mkdir($dirPath . "/backup") && !is_dir($dirPath . "/backup")) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dirPath . "/backup"));
            }
        }
        if( !is_dir($dirPath . "/backup/" . $filename) ) {
            if (!mkdir($dirPath . "/backup/" . $filename) && !is_dir($dirPath . "/backup/" . $filename)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dirPath . "/backup/" . $filename));
            }
        }
        $backupFileName = date("Ymd.His");
        copy($path, "$dirPath/backup/$filename/$backupFileName.bak.cpf"); // easier to exclude in phpstorm if it has extension
    }

    // yaml is the new preferred format
    $projectArray = array();

    if (isset($_SESSION["editor"])) {
        //include all element so you can unserialize them
        //the class must present before
        $config = getHsConfig();

        try {
            $files = glob($config->getBasePath . "elements/*.php");
            foreach ($files as $file) include_once $file;
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage() . "<br>\n";
        }

        foreach ($_SESSION["editor"] as $key => $value) {
            if ($f = unserialize($value['property'])) {
                $projectArray[$key]['form'] = $f->getData();
                if (isset($value["elements"]) && is_array($value["elements"])) {
                    foreach ($value["elements"] as $key2 => $value2) {
                        if ($e = unserialize($value['elements'][$key2])) {
                            $projectArray[$key]['elements'][$key2] = $e->getData();
                        }
                    }
                }
            }
        }
    }


    file_put_contents($path, "/*JSON*/\n" . json_encode($projectArray, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES));


    // save filename
    $_SESSION['projectname'] = $path;
}

if (isset($_REQUEST["projectnew"]) && $_REQUEST["projectnew"] != "") {
    $_SESSION['editor'] = null;
    unset($_SESSION['editor']);
    $_SESSION['projectname'] = "";
}

if (isset($_REQUEST["projectload"]) && $_REQUEST["projectload"] != "") {
    $path = $_REQUEST["projectload"];
    $parser = new cpfFileParser();
    $formulare = $parser->parseCpf($path);
    $_SESSION["editor"] = $formulare;

    $_SESSION['projectname'] = $_REQUEST["projectload"];
}
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type"
          content="text/html; charset=<?php if ($config->isUtf8()) echo 'UTF-8'; else echo 'ISO-8859-15'; ?>">
    <script type="text/javascript">
        var session_name = '<?php echo session_name(); ?>';
    </script>
    <link type="text/css" href="css/start/jquery-ui-1.10.0.custom.min.css" rel="stylesheet"/>
    <link type="text/css" href="css/editor.css" rel="stylesheet"/>
    <link type="text/css" href="css/em.selectbox.css" rel="stylesheet"/>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/jquery.ba-resize.min.js"></script>

    <script type="text/javascript" src="js/jquery.layout.min.js"></script>
    <script type="text/javascript" src="js/jquery.rightClick.js"></script>
    <script type="text/javascript" src="js/jquery.text-effects.js"></script>
    <script type="text/javascript" src="js/em.selectbox.js"></script>
    <script type="text/javascript" src="js/editorV3.js"></script>

</head>
<body>
<div class="menutop right" id="menutopright_popup">
    <button id="formtaborder">set tab-order</button>
    <button id="formsql">Sql for this form</button>

    <button id="formlanguageids">set language ids for this project</button>
    <button id="formlanguagearray">language array for this project</button>
    <button id="editorsessionvariablen">session variables</button>
    <div id="timezone" style="padding:5px; ">
        <table>
            <tr>
                <td nowrap>PHP:</td>
                <td nowrap><?php echo $config->getTimezoneInfoPHP(); ?></td>
            </tr>
            <tr>
                <td nowrap>DB:</td>
                <td nowrap><?php echo $config->getTimezoneInfoDB(); ?></td>
            </tr>
            <tr>
                <td nowrap>Session name:</td>
                <td nowrap><?php echo session_name(); ?></td>
            </tr>
            <tr>
                <td nowrap>Session id:</td>
                <td nowrap><?php echo session_id(); ?></td>
            </tr>
        </table>
    </div>
</div>
<div class="ui-layout-north ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header">
    <div style="float:left; ">
        <button id="editornew">New</button>
        <button id="editorload">Load</button>
        <button id="editorsaveas">Save as</button>
        <button id="editorsave">Save</button>
        <button id="formlink"
                data-projectname="<?= $config->getBaseUrl() ?>/interpreter.php?projecturlload=<?= urlencode($_SESSION['projectname'] ?? ""); ?>">
            Link
        </button>
    </div>
    <div style="float:right; ">
        <div id="projectname" style="line-height:17px; ">
            <table>
                <tr>
                    <td>Project:</td>
                    <td><?php echo(trim($_SESSION['projectname'] ?? "") != "" ? $_SESSION['projectname'] ?? "" : 'not saved'); ?></td>
                </tr>
            </table>
        </div>
        <button id="fromadd">new form</button>
        <button id="fromdel">delete form</button>
        <button id="formpropertys">form properties</button>
        <button id="menutopright">...</button>
    </div>
    <div style="clear:both; "></div>
</div>
<div class="ui-layout-west" id="controlnavi">
    <?php
    $config = getHsConfig();
    include($config->getBasePath . "editor_controls.php");
    ?>
</div>
<div class="ui-layout-east" id="propertys">
    <div style="width:100%; ">
        <div id="allforms" style="padding:5px; ">
            Forms:
            <select id="formsall" style="width:100%; display:block; "></select>
        </div>
    </div>
    <div>
        <div id="allelements" style="padding:5px; ">
            Elements:
            <select id="elementsall" style="width:100%; display:block; "></select>
        </div>
    </div>
    <div style="border-bottom:2px solid black; ">
        <div style="padding:5px; ">
            <button id="resetposition" type="button">Set to 0,0</button>
        </div>
    </div>
    <div style="border-bottom:2px solid black; ">
        <div style="padding:5px; ">
            You can use "SQL::" in each field you like to execute a sql request to the database. This only work proper in fields that not explicit declare to contain a sql query.<br>
            In the css field you can type "SQL::Select 'color:red; '".<br>
            <br>
            You can use "PHP::" in each field you like to execute php code. You have to "echo" a value whitch will use in formedit as new value for this field.<br>
            In the css field you can type "PHP::echo 'color:red; ';".
        </div>
    </div>
    <div id="propertybox"></div>
</div>
<div class="ui-layout-center" id="desktop">
    <div id="destkoptabs"></div>
</div>


<div id="menucontrol" class="contextmenu">
    <input type="hidden" name="id" value="">
    <div class="copy"><a href="#copy">Copy</a></div>
    <div class="copyinterformular"><a href="#copyinterformular">Copy Clipboard</a></div>
    <hr>
    <div class="delete"><a href="#delete">Delete</a></div>
</div>

<div id="menudesktop" class="contextmenu">
    <div class="paste"><a href="#paste">Past</a></div>
    <div class="pasteinterformular"><a href="#pasteinterformular">Past Clipboard</a></div>
    <!--<div class="test"><a href="#test">test</a></div>-->
</div>

<div id="dialog-editor-copyinterformular-click" title="Copy" style="display:none; ">
    Select all with Ctrl-a and copy the text into the clipboard with Ctrl-c
    <textarea id="textarea_copyinterformular"></textarea>
</div>

<div id="dialog-editor-pasteinterformular-click" title="Past" style="display:none; ">
    Paste the code with Ctrl-v from the clipboard and click save
    <textarea id="textarea_pasteinterformular"></textarea>
</div>

<div id="dialog-editor-new-click" title="New project" style="display:none; ">
    Do you really start a new project?
    <form id="dialog-editor-new-click-form" action="editor.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="projectnew" value="1">
    </form>
</div>

<?php
/*
<div id="dialog-editor-load-click" title="Load project" style="display:none; ">
	<form id="dialog-editor-load-click-form" action="editor.php" method="POST">
        <input type="hidden" name="session_name" value="<?php echo session_name(); ?>">
    	Projectfiles:<br>
    	<select id="select_projectload" name="projectload" size="10" style="width:475px; ">
            <?php
            $hsconfig=getHsConfig();
            $files=$hsconfig->getFiles();
            if(is_array($files))
            {
                //for($x=0;$x<count($files);$x++)
                foreach($files as $file)
                {
                    echo '<option>'.$file.'</option>';
                }
            }
            ?>
    	</select>
	</form>
</div>
*/
?>
<div style="display:none; ">
    <form id="dialog-editor-load-click-form" action="editor.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="session_name" value="<?php echo session_name(); ?>">
        <input type="hidden" id="select_projectload" name="projectload" value="">
    </form>
</div>

<?php
/*
<div id="dialog-editor-save-click" title="Save project" style="display:none; ">
	<form id="dialog-editor-save-click-form" action="editor.php" method="POST">
        <input type="hidden" name="session_name" value="<?php echo session_name(); ?>">
    	Projectfiles:<br>
    	<select id="select_projectsave" size="9" style="width:475px; ">
            <?php
            $hsconfig=getHsConfig();
            $files=$hsconfig->getFiles();
            if(is_array($files))
            {
                //for($x=0;$x<count($files);$x++)
                foreach($files as $file)
                {
                    echo '<option>'.$file.'</option>';
                }
            }
            ?>
    	</select>
    	<input name="projectsave" id="textbox_projectsave" type="text" style="width:475px; " value="<?php echo $_SESSION['projectname']; ?>">

	</form>
</div>
*/
?>
<div style="display:none; ">
    <form id="dialog-editor-save-click-form" action="editor.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="session_name" value="<?php echo session_name(); ?>">
        <input type="hidden" name="projectsave" id="textbox_projectsave"
               value="<?php echo(trim($_SESSION['projectname'] ?? "") != "" ? $_SESSION['projectname'] ?? "" : ''); ?>">
    </form>
</div>


<div id="blocksite" class="ui-widget-overlay"
     style="display:block; z-index:1000; position: absolute; left:0px; top:0px; right:0px; bottom:0px; margin:auto; padding:200px; "></div>

<?php
if (isset($_SESSION["editor"])) {
    $config = getHsConfig();
    $d = opendir($config->getBasePath . "elements");
    while ($file = readdir($d)) {
        if (is_file($config->getBasePath . "elements/" . $file) && substr(strtolower($file), strlen($file) - 4) == ".php") {
            try{
                include_once($config->getBasePath . "elements/" . $file);
            } catch (Exception $e) {
                echo 'Exception: '.$e->getMessage()."<br>\n";
            }
        }
    }
    closedir($d);

    foreach ($_SESSION["editor"] as $key => $value) {
        if (isset($_SESSION["editor"][$key]["elements"]) && is_array($_SESSION["editor"][$key]["elements"])) {
            foreach ($_SESSION["editor"][$key]["elements"] as $key2 => $value2) {
                try {
                    $e = unserialize($_SESSION['editor'][$key]['elements'][$key2]);
                    if ($e) {
                        echo $e->editorBeforeRender();
                    }
                } catch (Exception $e) {
                    echo 'Exception: '.$e->getMessage()."<br>\n";
                }
            }
        }
    }
}
?>
<script type="text/javascript">
    $().ready(function () {
        <?php

        if (isset($_SESSION["editor"])) {
            $config = getHsConfig();

            $startkey = "";
            foreach ($_SESSION["editor"] as $key => $value) {
                if ($startkey == "")
                    $startkey = $key;

                echo 'addNewTab2("' . $key . '"); ';
                echo 'setTabName("' . $key . '", "' . editor_refreshelementform($key) . '"); ';

                if (isset($_SESSION["editor"][$key]["elements"]) && is_array($_SESSION["editor"][$key]["elements"])) {
                    foreach ($_SESSION["editor"][$key]["elements"] as $key2 => $value2) {
                        try {
                            $e = unserialize($_SESSION['editor'][$key]['elements'][$key2]);
                            if ($e) {
                                $elementhtml = trim(editor_loadelement($key, $key2));
                                if ($elementhtml != "") {
                                    //echo $elementhtml;
                                    $elementhtml = str_replace("'", "\'", $elementhtml);
                                    $elementhtml = str_replace("\n", "", $elementhtml);
                                    $elementhtml = str_replace("\r", "", $elementhtml);
                                    $elementhtml = str_replace("\t", " ", $elementhtml);
                                    echo "
                            initElement('" . $elementhtml . "','" . $key . "'); 
                            ";
                                }
                            }
                        } catch (Exception $e) {
                            echo 'Exception: '.$e->getMessage()."<br>\n";
                        }
                    }
                }
            }

            echo 'selectTab("' . $startkey . '"); ';


        } else {
            echo "editorNew(); ";
        }

        ?>

        $('#blocksite').css('display', 'none');
    });
</script>
</body>
</html>
