<?php

require_once __DIR__ . "/../core/hsconfig.php";
require_once __DIR__ . "/shopinterface.php";

require_once __DIR__ . "/../core/hsproperty.php";
require_once __DIR__ . "/../core/hsbaseelement.php";
require_once __DIR__ . "/../core/hsbasecontrol.php";
require_once __DIR__ . "/../core/hsbasetab.php";
require_once __DIR__ . "/commonincludes.php";

class cpfFileParser
{
    /** @var cpfFileParser */
    private static $_instance = null;
    public static function getInstance()
    {
        if (static::$_instance === null) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    public function parseCpf($path)
    {
        $this->convertBaseUrlToPath($path);

        if (!preg_match('~^https?://~', $path)) { // we don't take urls.
            if (substr($path, -4) != ".cpf") {
                // without extension .cpf
                if (file_exists($path . ".cpf")) {
                    $path .= ".cpf";
                }
            } else {
                // with extension .cpf
                if (!file_exists($path)) {
                    $path = substr($path, 0, -4);
                } else {
                }
            }

            if (!file_exists($path)) {
                echo "PROJECT NOT FOUND $_REQUEST[projectload] ($path)";
                die;
            }
        }

        $content = file_get_contents($path);
        $fileType = "serialized";

        if (preg_match('~^(/[*])?JSON([*]/)?~', $content, $match)) {
            $fileType = "json";
            $content = substr($content, strlen($match[0]));
        }

        if (preg_match('~^---~', $content)) {
            $fileType = "yaml";
        }

        switch ($fileType) {
            case "json" :
            case "yaml" :
                // include all element so you can unserialize them
                // the class must present before
                $config = hsConfig::getInstance();

                $files = glob($config->getBasePath . "elements/*.php");
                foreach ($files as $file)
                {
                    if($file!="." && $file!="..")
                    {
                        try
                        {
                            include_once $file;
                        }
                        catch(\Exception $e)
                        {
                        }
                    }
                }

                // parse content based on extension type.
                $functionMap = array(
                    "json" => function () use ($content) {
                        $arr = json_decode($content, true);
                        return $arr;
                    },
                    "yaml" => function () use ($path) {

                        return Spyc::YAMLLoad($path);

                        //return yaml_parse_file($path);
                    }
                );
                $projectArray = $functionMap[$fileType]();

                $form = array();
                if ($projectArray) {
                    foreach ($projectArray as $formId => $formData) { // formData is array('form' => array(...), 'elements' => array(...))
                        $className = $formData['form']['classname'];
                        /** @var baseelement $f */
                        $f = new $className();
                        $f->setData($formData['form']); // form is array('classname' => '...', 'property' => array(...))
                        $form[$formId]['property'] = serialize($f);

                        if (isset($formData['elements'])) {
                            foreach ($formData['elements'] as $elementId => $elementData) {
                                $className = $elementData['classname'];
                                /** @var basecontrol $e */
                                $e = new $className();
                                $e->setData($elementData);
                                $e->interpreterLoadLang();
                                $form[$formId]['elements'][$elementId] = serialize($e);
                            }
                        }
                    }
                }
                break;
            default:
                $form = unserialize($content);
                break;
        }

        return $form;
    }

    /**
     * Send a BASE url and get a BASE path back
     * @param string $path  BASE url, if it is path already its ok. out, will have the path in the end, or the original url if couldn't figure out relative paths
     * @return bool  true if resulting file path exists.
     */
    public function convertBaseUrlToPath(&$path)
    {
        $si = shopInterface::getInstance();

        $baseDir = $si->getModulesDir();
        $baseUrl = $si->getModulesUrl();

        if(substr($path,0,strlen('https://')=="https://") ||
            substr($path,0,strlen('http://')=="http://") ||
            substr($path,0,strlen('/')=="/")
        )
        {
            // this means path is relative to modules dir, so make it absolute by prepending modules url
            $path = rtrim($baseUrl, "/") . "/$path";
        }

        // make sure baseUrl has matching protocol, for successful replacement later.
        if (preg_match('`^(http?://|https?://)`', $path, $match)) {
            $protocol = $match[0];
            $baseUrl = preg_replace('`^https?://`', $protocol, $baseUrl);
        }

        $path = str_replace($baseUrl, $baseDir, $path);

        $fileExists = file_exists($path);

        if ($fileExists) {
            $path = realpath($path);
        }

        return $fileExists;
    }
}
