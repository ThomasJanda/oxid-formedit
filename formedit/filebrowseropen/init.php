<?php
//always the same session name
$sessionname="cpformedit_filesystem";
if(isset($_REQUEST['session_name']) && $_REQUEST['session_name']!="")
    $sessionname=$_REQUEST['session_name'];
$_REQUEST['session_name']=$sessionname;

//init system
require_once __DIR__ . "/../core/hsinit.php";

/**
 * absolute path to the modules folder
 * @return string
 */
function getRootDir()
{
    $hsconfig = getHsConfig();
    return $hsconfig->modulesFolder;
}

/**
 * generate all nessesary hidden paramter
 * @return string
 */
function getHiddenParameter()
{
    $html='
    <input type="hidden" name="returnfunction" value="'.$_REQUEST['returnfunction'].'">
    ';
    return $html;
}

/**
 * generate directory listing
 *
 * @param      $root
 * @param bool $start
 *
 * @return string[]
 */
function getDirectoryList($root,$start=true)
{
    $list = array();

    if($root=="")
    {
        return $list;
    }
    $root = rtrim($root,"/");

    $hsconfig = getHsConfig();

    if (is_dir($root)) {
        if ($dh = opendir($root)) {
            while (($file = readdir($dh)) !== false)
            {
                //first level
                if($file!="." && $file!=".." && is_dir($root."/".$file))
                {
                    if(file_exists("$root/$file/$hsconfig->formeditFolderName")) {
                        $list[] = "$root/$file/$hsconfig->formeditFolderName";
                    }
                    else
                    {
                        $tmp = getDirectoryList("$root/$file",false);
                        if(count($tmp)>0)
                        {
                            $list = array_merge($list,$tmp);
                        }
                    }
                    //if vendormetadata.php exists, than search within subdirectory
                    /*
                    if(file_exists($root."/".$file."/vendormetadata.php"))
                    {
                        $tmp = getDirectoryList("$root/$file",false);
                        if(count($tmp)>0)
                        {
                            $list = array_merge($list,$tmp);
                        }
                    }
                    elseif(file_exists("$root/$file/$hsconfig->formeditFolderName")) {
                        $list[] = "$root/$file/$hsconfig->formeditFolderName";
                    }
                     */
                }

            }
            closedir($dh);
        }
    }


    if($start==true)
    {

        //remove root
        $list2=array();
        foreach($list as $d)
        {
            $list2[]=str_replace($root,"",$d);
        }
        sort($list2);
        $list=$list2;
    }
    return $list;
}

/**
 * generates filelist
 *
 * @param $root
 *
 * @return string[]
 */
function getFileList($root)
{
    $list=array();
    if (is_dir($root)) {
        if ($dh = opendir($root)) {
            while (($file = readdir($dh)) !== false)
            {
                //first level
                if($file!="." && $file!=".." && is_file($root."/".$file))
                {
                    if(substr(strtolower($file),strlen($file)-4)==".cpf")
                    {
                        $list[]=$file;
                    }
                }

            }
            closedir($dh);
        }
    }
    sort($list);
    return $list;
}
