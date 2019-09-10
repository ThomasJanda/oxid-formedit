<?php
require_once __DIR__ . "/init.php";

$directories = getDirectoryList(getRootDir());

//load from last page
$selectedDirectory = (isset($_REQUEST['directory'])?$_REQUEST['directory']:'');
if($selectedDirectory=="" && isset($_SESSION['directory']))
    $selectedDirectory=$_SESSION['directory'];

//prove if directory is present
if(!is_dir(getRootDir().$selectedDirectory))
    $selectedDirectory="";

//if present, save for next call
$_SESSION['directory']=$selectedDirectory;

?><!DOCTYPE HTML>
<html lang="en" >
<head>
    <title>Open formedit project</title>
    <style>
        body, html
        {
            font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size:12px;
        }
        input
        {
            width:calc(100% - 10px);
            padding:1px;
            margin:0;
        }
        h2
        {
            margin:0;
            padding:0;
            font-size:14px;
            margin-bottom:5px;
        }
        img
        {
            border:0;
            padding:0;
            margin:0;
            vertical-align : middle;
        }
        div.directories
        {
            float:left;
            width:250px;
            border-right:1px solid lightgrey;
        }
        div.files
        {
            float:right;
            width:calc(100% - 255px);
        }
        div.block
        {
            margin-bottom:10px;
        }
        button
        {
            border: 0;
            background: transparent;
            text-align: left;
            margin: 0;
            font-size: 12px;
            cursor:pointer;
            width:100%;
            padding:2px;
        }
        button.selected
        {
            color:blue;
            font-weight:bold;
        }
        button.hidden
        {
            display:none;
        }
        button:hover
        {
            background-color:lightgrey;
        }
        button.directory
        {
            border-bottom:1px solid lightgrey;
        }
    </style>
</head>
<body>
<div>
    <div class="directories">
        <h2>Directories</h2>
        <div>
            <input placeholder="Search" type="text" onkeyup="directorySearch(this.value);">
        </div>
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <?php
            echo getHiddenParameter();

            foreach($directories as $directory)
            {
                echo '<button type="submit" class="directory '.($directory==$selectedDirectory?'selected':'').'" name="directory" value="'.$directory.'">';
                echo '<img src="img/folder.png">';
                echo '&nbsp;';
                echo substr(dirname($directory), 1);
                echo '</button>';
            }
            ?>
        </form>
    </div>
    <div class="files">
        <div class="block">
            <h2>Path</h2>
            <div>
                <?php echo $selectedDirectory; ?>
            </div>
        </div>
        <div class="block">
            <h2>Files</h2>
            <?php
            $found=false;
            if($selectedDirectory!="")
            {
                $files = getFileList(getRootDir().$selectedDirectory);
                foreach($files as $file)
                {
                    $found=true;
                    $onclickFile = json_encode(preg_replace('~[\\\\/]~', DIRECTORY_SEPARATOR, getRootDir().$selectedDirectory."/".$file));
                    echo "<button type=button onclick='selectFile($onclickFile);' name=directory >";
                    echo '<img src="img/o.png">';
                    echo '&nbsp;';
                    echo $file;
                    echo '</button>';
                }
            }
            if($found==false)
            {
                echo 'No file found';
            }
            ?>
        </div>
    </div>
    <div style="clear:both; "></div>
</div>

<script type="text/javascript">
    function OnClose()
    {
        if(window.opener != null && !window.opener.closed)
        {
            window.opener.unblocksite();
        }
    }
    window.onunload = OnClose;

    function selectFile(path)
    {
        window.opener.<?php echo $_REQUEST['returnfunction']; ?>(path);
        window.close();
    }
    function directorySearch(value)
    {
        var list = document.getElementsByClassName('directory');
        for (var i = 0; i < list.length; ++i)
        {
            var item = list[i];
            var v = item.value;
            if(v.search(value)==-1)
            {
                item.classList.add('hidden');
            }
            else
            {
                item.classList.remove('hidden');
            }
        }
    }
</script>
</body>
</html>
