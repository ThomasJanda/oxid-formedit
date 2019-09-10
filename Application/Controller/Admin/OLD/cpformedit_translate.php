<?php

class cpformedit_translate extends oxAdminDetails
{
    protected $_template="cpformedit_translate.tpl";
    
    public function render()
    {
        parent::render();

        $filesadmin=array();
        $files=array();
        $abbrs=array();
        
        $aLanguages = oxRegistry::getLang()->getLanguageArray();
        foreach($aLanguages as $oVal)
        {
            $langid = $oVal->id;
            $abbr   = $oVal->abbr;
            $abbrs[$langid]=$abbr;
        }
        
        $path=realpath(dirname(__FILE__))."/../../formedit/files";
        $pathlang=realpath(dirname(__FILE__))."/../../formedit/lang";
        $d=opendir($path);
        while ($file = readdir ($d))
        {
            if(is_file($path."/".$file))
            {
                $proto=array();
                $langs=array();
                foreach($aLanguages as $oVal)
                {
                    $langid = $oVal->id;
                    $pathlangfile = $pathlang."/".$file."_".$langid.".php";
                    //echo $pathlangfile."<br>";
                    if(file_exists($pathlangfile))
                    {
                        $lang=array();
                        include_once($pathlangfile);
                        $langs[$langid]=$lang;
                        foreach($lang as $lid => $property)
                        {
                            foreach($property as $name => $value)
                            {
                                if(is_array($value))
                                {
                                    foreach($value as $index => $v)
                                    {
                                        $proto[$lid][$name][$index]="";
                                    }
                                }
                                else
                                {
                                    $proto[$lid][$name]="";
                                }    
                            }
                        }
                    }
                }
                
                
                foreach($aLanguages as $oVal)
                {
                    $langid = $oVal->id;
                    //$abbr   = $oVal->abbr;
                    $files[$file][$langid]['file']=$file."_".$langid.".php";
                    $files[$file]['path']=$pathlang;
                    $files[$file][$langid]['filepath']=$files[$file]['path']."/".$files[$file][$langid]['file'];
                    $files[$file]['proto']=$proto;
                    $files[$file][$langid]['lang']=array();
                    
                    if(file_exists($files[$file][$langid]['filepath']))
                    { 
                        //echo "ja";
                        $lang=array();
                        //include_once($files[$file][$langid]['filepath']);
                        $lang=$langs[$langid];
                        //print_r($lang);
                        
                        foreach($lang as $lid => $property)
                        {
                            foreach($property as $name => $value)
                            {
                                if(is_array($value))
                                {
                                    foreach($value as $index => $v)
                                    {
                                        $files[$file][$langid]['lang'][$lid][$name][$index]=str_replace("'",'&apos;',$v);
                                    }
                                }
                                else
                                {
                                    $files[$file][$langid]['lang'][$lid][$name]=str_replace("'",'&apos;',$value);
                                }    
                            }
                        }
                        
                        /*
                        $tmp=array();
                        foreach($lang as $key=>$value)
                        {
                            $tmp[trim($key)]=str_replace("'",'&apos;',$value);
                        }
                        $lang=$tmp;
                        
                    
                        $keys=array();
                        foreach($lang as $key => $value)
                        {
                            if(!in_array($key,$files[$file]['keys']))
                                $files[$file]['keys'][]=$key;
                        } 
                        
                        $files[$file][$langid]['lang']=$lang;
                        */
                    }
                }
            }
        }
        closedir($d);

        /*
        echo '<pre>';
        print_r($files);
        echo '</pre>';
        */
        
        $path=realpath(dirname(__FILE__))."/../../formedit/langadmin";
        foreach($aLanguages as $oVal)
        {
            $langid = $oVal->id;
            //$abbr   = $oVal->abbr;
            $filesadmin[$langid]['file']=$langid.".php";
            $filesadmin['path']=$path;
            $filesadmin[$langid]['filepath']=$filesadmin['path']."/".$filesadmin[$langid]['file'];
            
            if(!isset($filesadmin['keys']))
                $filesadmin['keys']=array();
            //echo $filesadmin[$langid]['filepath'];
            if(file_exists($filesadmin[$langid]['filepath']))
            {
                $lang=array();
                include($filesadmin[$langid]['filepath']);
                $tmp=array();
                foreach($lang as $key=>$value)
                {
                    $tmp[trim($key)]=str_replace("'",'&apos;',$value);
                }
                $lang=$tmp;
                
            
                $keys=array();
                foreach($lang as $key => $value)
                {
                    if(!in_array($key,$filesadmin['keys']))
                        $filesadmin['keys'][]=$key;
                } 
                
                $filesadmin[$langid]['lang']=$lang;
            }
        }

                
        
        $this->_aViewData['abbrs']=$abbrs;
        $this->_aViewData['langdata']=$files;
        $this->_aViewData['langdataadmin']=$filesadmin;
        
        return $this->_template;
        die("");
        /*
        $abbrs=array();
        $files=array();
        $aLanguages = oxRegistry::getLang()->getLanguageArray();
        foreach($aLanguages as $oVal)
        {
            $langid = $oVal->id;
            $abbr   = $oVal->abbr;
            
            if(!in_array($abbr,$abbrs))
                $abbrs[]=$abbr;
            
            $tmp = oxRegistry::getLang()->getAdminLangFilesPathArray($langid);
            foreach($tmp as $file)
            {
                if(file_exists($file))
                {
                    $aLang=array();
                    include($file);
                    $charset=$aLang['charset'];
                    unset($aLang['charset']);

                    $tmp=array();
                    foreach($aLang as $key=>$value)
                    {
                        $tmp[trim($key)]=str_replace("'",'&apos;',$value);
                    }
                    $aLang=$tmp;
                    
                    if(strtolower($charset)!='utf-8')
                    {
                        $aLang=$this->_recodeLangArray( $aLang, $charset );
                    }
                    
                    $path = dirname(dirname($file));

                    if(!isset($files['Admin'][$path][basename($file)]['keys']))
                        $files['Admin'][$path][basename($file)]['keys']=array();
                    
                    $keys=array();
                    foreach($aLang as $key => $value)
                    {
                        if(!in_array($key, $files['Admin'][$path][basename($file)]['keys']))
                            $files['Admin'][$path][basename($file)]['keys'][]=$key;
                    }                    
                    
                    $files['Admin'][$path][basename($file)][$abbr]=$aLang; 
                }
            }

            $tmp = oxRegistry::getLang()->getLangFilesPathArray($langid);
            foreach($tmp as $file)
            {
                if(file_exists($file))
                {
                    $aLang=array();
                    include($file);
                    $charset=$aLang['charset'];
                    unset($aLang['charset']);

                    $tmp=array();
                    foreach($aLang as $key=>$value)
                    {
                        $tmp[trim($key)]=str_replace("'",'&apos;',$value);
                    }
                    $aLang=$tmp;
                    
                    if(strtolower($charset)!='utf-8')
                    {
                        $aLang=$this->_recodeLangArray( $aLang, $charset );
                    }
                    
                    $path = dirname(dirname($file));

                    if(!isset($files['Shop'][$path][basename($file)]['keys']))
                        $files['Shop'][$path][basename($file)]['keys']=array();
                    
                    $keys=array();
                    foreach($aLang as $key => $value)
                    {
                        if(!in_array($key, $files['Shop'][$path][basename($file)]['keys']))
                            $files['Shop'][$path][basename($file)]['keys'][]=$key;
                    }                    
                    
                    $files['Shop'][$path][basename($file)][$abbr]=$aLang; 
                }
            }
        }
        
        $this->_aViewData['abbrs']=$abbrs;
        $this->_aViewData['langdata']=$files;
        
        return $this->_template;
        */
    }
    
    
    

    public function save()
    {

        $path = oxRegistry::getConfig()->getRequestParameter('path');
        $filename = oxRegistry::getConfig()->getRequestParameter('filename');
        $langall = oxRegistry::getConfig()->getRequestParameter('lang');
        
        foreach($langall as $langid=>$langvars)
        {
            $filename1=$filename."_".$langid.".php";
            
            $savepath=$path."/".$filename1;
            
            if(!file_exists($savepath."_orig"))
            {
                @rename($savepath,$savepath."_orig");
            }
            
            $datei = fopen($savepath,"w");
            fwrite($datei,'<?php'."\n\n");

            $tmp='$lang'." = array(\n";
            foreach($langvars as $lid => $lang)
            {
                $tmp.="'".$lid."' => array(\n";
                foreach($lang as $name => $value)
                {
                    if(is_array($value))
                    {
                        $tmp.="\t'".$name."' => array(\n";
                        foreach($value as $n=>$v)
                        {
                            $tmp.="\t\t'".$n."' => '".str_replace('"','\"',$v)."',\n"; 
                        }
                        $tmp.="\t),\n";
                    }
                    else
                    {
                        $tmp.="\t'".$name."' => '".str_replace('"','\"',$value)."',\n";       
                    }
                }
                $tmp.="),\n";
            }
            $tmp.=");\n";
               
            fwrite($datei,$tmp);
            /*
            foreach($langvars as $key=>$val)
            {
                $val=str_replace("'",'&apos;',$val);
                fwrite($datei,"'".$key."' => '".$val."',"."\n");
            }
            
            fwrite($datei,");"."\n");
            
            */
            fwrite($datei,"?>");
            fclose($datei);
        }
        
    }
    public function saveadmin()
    {
        //die("");
        $path = oxRegistry::getConfig()->getRequestParameter('path');
        $lang = oxRegistry::getConfig()->getRequestParameter('lang');
        
        foreach($lang as $langid=>$langvars)
        {
            
            $filename=$langid.".php";
            
            $savepath=$path."/".$filename;
            //echo $savepath;
            //die("");
            if(!file_exists($savepath."_orig"))
            {
                @rename($savepath,$savepath."_orig");
            }
            
            $datei = fopen($savepath,"w");
            fwrite($datei,'<?php'."\n");

            fwrite($datei,'$lang = array('."\n");
            
            foreach($langvars as $key=>$val)
            {
                $val=str_replace("'",'&apos;',$val);
                fwrite($datei,"'".$key."' => '".$val."',"."\n");
            }
            
            fwrite($datei,");"."\n");
            fwrite($datei,"?>");
            fclose($datei);
        }
        
    }    
}
