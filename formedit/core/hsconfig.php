<?php
require_once __DIR__ . "/hsdb.php";

/**
 * Class hsconfig
 */
class hsconfig
{
    static private $instance          = null;
    public         $getBasePath       = __DIR__ . "/../";
    protected      $urlroot           = "";
    protected      $dirroot           = "";
    public      $sShopDir = "";
    public      $sShopURL = "";
    protected      $dbhost            = "";
    protected      $dbport            = "";
    protected      $dbname            = "";
    protected      $dbuser            = "";
    protected      $dbpass            = "";
    protected      $dbutf8            = false;
    protected      $dbservertimeshift = "";
    protected      $dbid              = null;


    protected $_editorMode = false;

    public function setEditorMode($value=true)
    {
        $this->_editorMode =$value;
    }

    public function getEditorMode()
    {
        return $this->_editorMode;
    }

    /**
     * @var \mysqli
     */
    protected $_oMySqlI = null;

    // santi trying to figure this out.
    /**
     * @var int
     */
    protected $_affectedrows = 0;
    protected $langsuffix    = null;
    protected $_lang         = null;
    protected $_langadmin    = null;
    /**
     * @var \basetab
     */
    private $_otab             = null;
    private $interpreterValues = [];

    /**
     * @var \hsdb
     */
    private $_oDB              = null;
    private $oOldTab           = null;

    public function __construct()
    {
        $pfad = __DIR__ . "/../config.inc.php";
        if (file_exists($pfad)) {
            include($pfad);
        }
    }

    public function getDb()
    {
        if($this->_oDB===null)
        {
            $this->_oDB = new hsdb($this->_getConnectionData());
        }
        return $this->_oDB;
    }
    protected function _getConnectionData() {
        $connectionData             = [];
        $connectionData['dbHost']   = $this->dbhost;
        $connectionData['dbPort']   = $this->dbport;
        $connectionData['dbName']   = $this->dbname;
        $connectionData['dbUser']   = $this->dbuser;
        $connectionData['dbPwd']    = $this->dbpass;
        $connectionData['iUtfMode'] = $this->dbutf8;

        return $connectionData;
    }

    public function startSessionHandling()
    {
        //session_start();
    }


    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    public function isUtf8()
    {
        $ret = $this->dbutf8;
        return $ret;
    }

    public function getTimezoneInfoPHP()
    {
        return date('d.m.Y H:i:s');
    }

    public function getTimezoneInfoDB()
    {
        $sqlstring = "select date_format( now() , '%d.%m.%Y %H:%i:%s' )";
        return $this->getScalar($sqlstring);
    }

    /**
     * @param $sqlstring
     *
     * @return mixed|null
     */
    public function getScalar($sqlstring)
    {
        return $this->getDb()->getOne($sqlstring);
    }

    /**
     * @param $sqlstring
     *
     * @return bool|mysqli_result|null
     */
    public function Execute($sqlstring)
    {
        return $this->getDb()->execute($sqlstring);
    }

    /**
     * @param \mysqli_result $rs
     */
    public function close($rs)
    {
        $this->getDb()->close($rs);
    }

    public function getOne($sqlstring)
    {
        return $this->getScalar($sqlstring);
    }

    public function escapeString($string)
    {
        return $this->getDb()->escapeString($string);
    }

    public function getRow($sqlstring, $AsObject = true)
    {
        return $this->getDb()->getRow($sqlstring,$AsObject);
    }

    /**
     * @param $sqlstring
     *
     * @return int
     */
    public function executeNoReturn($sqlstring)
    {
        return $this->getDb()->executeNoReturn($sqlstring);
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->getDb()->getAffectedRows();
    }

    // todo: replace all globals for this function.

    /**
     * @param $sName
     *
     * @return null
     */
    public function getConfigParam($sName)
    {
        if (isset($this->$sName)) {
            return $this->$sName;
        }

        return null;
    }

    public function setInterpreterValue($key, $value)
    {
        $this->interpreterValues[$key] = $value;
    }

    public function getBaseUrl()
    {
        return rtrim($this->urlroot, "/");
    }


    public function getProjectBaseUrl()
    {
        if ($ret = $this->getProjectBaseDir()) {

            // check if path is link. if it is not, then convert it to link.
            if (strpos($ret, "http") !== 0) {
                $uri = shopInterface::getInstance()->getModulesDir();
                $url = shopInterface::getInstance()->getModulesUrl();
                // replace
                $ret = str_replace($uri, $url, $ret);
            }

        }

        return $ret;
    }

    public function getProjectBaseDir()
    {
        if ($ret = $this->getInterpreterValue("projectname", true)) {
            $ret = dirname($ret);

            // check if path is link. if it is, then convert it to absolute path.
            if (strpos($ret, "http") === 0) {
                $uri = shopInterface::getInstance()->getModulesDir();
                $url = shopInterface::getInstance()->getModulesUrl();
                // replace
                $ret = str_replace($url, $uri, $ret);
            }
        }

        return rtrim($ret, "/");
    }

    /**
     * @param      $key
     * @param bool $bIrrelevant
     *
     * @return mixed
     */
    public function getInterpreterValue($key, $bIrrelevant = false)
    {
        if (array_key_exists($key, $this->interpreterValues)) {
            return $this->interpreterValues[$key];
        } else {
            if ($bIrrelevant == false) {
                echo '<pre>';
                debug_print_backtrace(0, 10);
                echo '</pre>';
                echo "cannot find interpreter value: $key";
                die;
            }

        }
    }

    public function getShopRootDir()
    {
        return shopInterface::getInstance()->getShopRootDir();
    }

    public function getShopRootUrl()
    {
        return shopInterface::getInstance()->getShopRootUrl();
    }

    public function getLang($name)
    {
        if ($this->_lang === null) {
            $interpreterid = $this->getInterpreterId();
            $this->_lang   = [];

            //load lang
            $languageedit = $this->getLanguageEdit();
            $file         = $this->getInterpreterValue("projectname", true) . "_$languageedit.php";

            $filepath = $this->getLanguageEditDir() . $file;

            if (file_exists($filepath)) {
                $lang = [];
                include_once($filepath);
                $this->_lang = $lang;
            }
        }
        if (isset($this->_lang[$name])) {
            $ret = $this->_lang[$name];

            return $ret;
        } else {
            return false;
        }
    }

    public function getInterpreterId()
    {
        global $interpreterid;

        return $interpreterid;
    }

    public function getLanguageEdit()
    {
        global $languageedit;
        if ($languageedit == "") {
            return "0";
        }

        return $languageedit;
    }

    public function getLanguageEditDir()
    {
        $path = $this->getBaseDir() . "/lang/";

        if ($projectDir = $this->getProjectBaseDir()) {
            $path = "$projectDir/";
        }

        return $path;
    }

    public function getBaseDir()
    {
        return rtrim($this->dirroot, "/");
    }


    public function getLangAdmin($name)
    {
        if ($this->_langadmin === null) {
            //load lang
            $languageadmin = $this->getLanguageAdmin();
            $filepath      = $this->getLanguageAdminDir() . $languageadmin . ".php";

            if (file_exists($filepath)) {
                $lang = [];
                include_once($filepath);
                $this->_langadmin = $lang;
            }
        }
        if (isset($this->_langadmin[$name])) {
            return $this->_langadmin[$name];
        } else {
            return $name . " - NOT FOUND";
        }
    }

    public function getLanguageAdmin()
    {
        global $languageadmin;
        if ($languageadmin == "") {
            return "0";
        }

        return $languageadmin;
    }

    public function getLanguageAdminDir()
    {
        return $this->getBaseDir() . "/langadmin/";
    }

    public function getFiles()
    {
        $path  = $this->getFilesDir();
        $files = [];
        if (file_exists($path)) {
            $verz = opendir($path);
            while ($file = readdir($verz)) {
                if (is_dir($path . $file) == false) {
                    $files[] = $file;
                }
            }
            closedir($verz);
        }
        if (is_array($files)) {
            asort($files);
        }

        return $files;
    }

    public function getFilesDir()
    {
        return $this->getBaseDir() . "/files/";
    }

    /**
     * @return basecontrol[]|null
     */
    public function getElements()
    {
        global $tmpElements;

        return $tmpElements;
    }

    /**
     *
     * @return mysqli
     */
    public function getDbId()
    {
        return $this->getDb()->getDbId();
    }

    public function getDbHost()
    {
        $ret = $this->dbhost;
        return $ret;
    }

    public function getDbUser()
    {
        $ret = $this->dbuser;
        return $ret;
    }

    public function getDbPass()
    {
        $ret = $this->dbpass;
        return $ret;
    }

    public function getDbName()
    {
        $ret = $this->dbname;
        return $ret;
    } // key is the host.

    public function getDbPort()
    {
        $ret = $this->dbport;
        return $ret;
    } //

    /**
     * Returns only the relative path to BASE/modules folder.
     * i.e. "module_name/formedit/"
     *
     * @return bool|string
     */
    public function getProjectRelativePath()
    {
        $pName = $this->getProjectName();

        return substr($pName, 0, strrpos($pName, "/"));
    }

    public function getProjectName()
    {
        $ini = $this->getIni();

        return $ini["project"];
    }

    /**
     * just call this at the beginning of the request, this does not mutate as the script advances.
     *
     * @return array
     */
    public function getIni()
    {
        // The "ini" array contains all keys from the request array, plus the mandatory values that we want to have at least set.
        $mandatoryValues = array(
            "index1value"       => "",
            "index2value"       => "",
            "kennzeichen1value" => "",
            "languageadmin"     => "",
            "languageedit"      => "",
            "startform"         => null,
            "startformname"     => null,
            "form"              => "", // can be id or customer id.
            "formularid"        => "", // same as form, but legacy
            "projectload"       => "",
            "projecturlload"    => "",
            "interpreterid"     => null,
            // "navi" => "NEW",
            "startparam"        => [],
            // this is the definitive parameter:
            "project"           => "",
            "oldformid"         => "", // if not empty, means user is navigating already.
            // enables debug mode, similar to property[debug]
            "debug"             => false,
            "through"           => null,
            "redirect"          => null
        );

        $ini = $_REQUEST + $mandatoryValues;

        // if users land on a page with a form id and an index1, assume navi=dit
        $ini["navi"] = $_REQUEST["navi"] ?? ($ini["index1value"] ? "EDIT" : "NEW");

        $ini["project"] = $ini["project"] ?: $ini["projectload"] ?: $ini["projecturlload"];
        $ini["project"] = $this->generateProjectName($ini["project"]);

        return $ini;
    }

    private function generateProjectName($project)
    {
        // todo: this is not compatible with the shops.
        if (strpos($project, "/") === false) {
            // using only file name, will search inside all modules inside the formedit folder.
            $cronjobsSearcher = cConfig3::get("cronjobsSearcher");
            $conf             = cConfig3::getInstance();
            $classes          = $cronjobsSearcher->searchInModules("formedit", "cpf"); // all formedit files in modules

            if (substr($project, -4) == ".cpf") {
                // remove extendsion because result of searchInModules has no extension
                $project = substr($project, 0, -4);
            }

            $project = strtolower($project);

            if (array_key_exists($project, $classes)) {
                // means we found the project! we must leave it as relative path from inside the modules folder, as it used to be before.
                $fullProjectPath = $classes[$project];
                // looks something like this:                                                       needs to look something like this:
                // /home/base/public_html/BASE/modules/cronjobmanager/formedit/cron_cronjobs.cpf    cronjobmanager/formedit/cron_cronjobs.cpf
                $niceProjectPath = str_replace($conf->getBaseModulesDir(), "", $fullProjectPath);
                $project         = $niceProjectPath;
            }

            $project = $this->cleanLink($project);
        }

        return $project;
    }

    /**
     * @param $link
     *
     * @return mixed|string
     */
    public function cleanLink($link)
    {
        //echo $link."<br>";
        $link = str_replace("http://", "#HTTP#", $link);
        $link = str_replace("https://", "#HTTPS#", $link);

        $link = str_replace("//", "/", $link);

        $p = explode("/", $link);
        $n = [];
        for ($x = 0; $x < count($p); $x++) {
            //echo $p[$x]."<br>";
            if ($p[$x] == "..") {
                unset($n[count($n) - 1]);
            } else {
                $n[] = $p[$x];
            }
        }
        $link = implode("/", $n);

        $link = str_replace("#HTTP#", "http://", $link);
        $link = str_replace("#HTTPS#", "https://", $link);

        return $link;
    }

    public function getNewForm()
    {
        global $newForm;

        return $newForm;
    }

    public function getInterpreterParameterGet()
    {
        $params = $this->getInterpreterParameters();

        return http_build_query($params);
    }

    private function getInterpreterParameters()
    {
        global $newForm;
        $interpreterid = $this->getInterpreterId();
        $languageedit  = $this->getLanguageEdit();
        $languageadmin = $this->getLanguageAdmin();
        $navi          = $this->getNewNavi(); // todo: see if this causes problems
        $through       = $this->getThroughValue();
        $redirect      = $this->getRedirectValue();

        $param                  = [];
        $param["interpreterid"] = $interpreterid;
        if ($newForm) {
            $param["formularid"] = $newForm->getTabId();
        }

        $param["index1value"]       = $this->getIndex1Value();
        $param["index2value"]       = $this->getIndex2Value();
        $param["kennzeichen1value"] = $this->getKennzeichen1Value();
        $param["startform"]         = $this->getInterpreterValue("startform");
        if ($startParam = $this->getInterpreterValue("startparam")) {
            $param["startparam"] = $startParam;
        }

        $param["navi"]         = $navi;
        $param[session_name()] = session_id();
        // $param["forcereloadid"] = uniqid("");

        if ($languageedit) {
            $param["languageedit"] = $languageedit;
        }
        if ($languageadmin) {
            $param["languageadmin"] = $languageadmin;
        }

        if ($through) {
            $param["through"] = $through;
        }

        if ($redirect) {
            $param["redirect"] = $redirect;
        }

        return $param;
    }

    public function getNewNavi()
    {
        global $newNavi;

        return $newNavi;
    }

    public function getThroughValue()
    {
        global $throughValue;

        return $throughValue;
    }

    public function getRedirectValue()
    {
        global $redirectValue;

        return $redirectValue;
    }

    public function getIndex1Value()
    {
        global $index1value;

        return $index1value;
    }

    public function getIndex2Value()
    {
        global $index2value;

        return $index2value;
    }

    public function getKennzeichen1Value()
    {
        global $kennzeichen1value;

        return $kennzeichen1value;
    }

    public function getInterpreterParameterPost()
    {
        $params = $this->getInterpreterParameters();
        $withId = ['interpreterid', 'formularid', 'index1value', 'index2value', 'kennzeichen1value', 'startparam', 'navi'];

        $param = "";
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k2 => $v2) {
                    $param .= "<input type=hidden name='{$key}[$k2]' value='$v2'>\n";
                }
            } else {
                $id    = in_array($key, $withId) ? "id='$key'" : "";
                $param .= "<input type=hidden $id name='$key' value='$value'>\n";
            }
        }

        $param .= "<input type=hidden id='elementiddelete' name='elementiddelete'>\n";

        return $param;
    }

    public function getInterpreterParameterArray()
    {
        return $this->getInterpreterParameters();
    }

    public function getTab()
    {
        global $newForm;

        return $newForm;
    }

    public function getOldTab()
    {
        return $this->oOldTab;
    }

    public function setOldTab($oTab)
    {
        $this->oOldTab = $oTab;
    }

    public function parseSQLString($sqlstring, $param = [])
    {
        $baseDir    = $this->getBaseDir();
        $sqlParsers = glob("$baseDir/sqlparser/*.php");
        /** @var interfacesqlparser[] $sqlParsers */
        $sqlParsers = array_filter(array_map(function ($sqlParser) {
            if (preg_match('~interfacesqlparser.php$~', $sqlParser)) {
                return null;
            } else {
                $className = substr(basename($sqlParser), 0, -4);

                return new $className();
            }
        }, $sqlParsers));

        foreach ($sqlParsers as $sqlParser) {
            $sqlstring = $sqlParser->parseSQLString_before($sqlstring, $param);
        }

        // if new navi hasn't been set then use old navi.
        $navi = $this->getNewNavi() ? : $this->getNavi();

        $mode = "";
        if (in_array($navi, ["NEW", "NEW_SAVE"])) {
            $mode = "NEW";
        } elseif (in_array($navi, ["EDIT", "EDIT_SAVE"])) {
            $mode = "EDIT";
        }
        $sqlstring = str_replace('#MODE#', $mode, $sqlstring);

        if ($value = $this->getInterpreterValue("startformname")) {
            $sqlstring = str_replace('#STARTFORMNAME#', $value, $sqlstring);
        }

        if ($value = $this->getInterpreterValue("startform")) {
            $sqlstring = str_replace('#STARTFORMNAME#', $value, $sqlstring);
        }

        $sqlstring = str_replace('#INDEX1#', $this->getIndex1Value(), $sqlstring);
        $sqlstring = str_replace('#INDEX2#', $this->getIndex2Value(), $sqlstring);
        $sqlstring = str_replace('#KENNZEICHEN1#', $this->getKennzeichen1Value(), $sqlstring);
        $sqlstring = str_replace('#CURRENTVALUE#', $value, $sqlstring);
        $sqlstring = str_replace('#EDITLANG#', $this->getLangColSuffix(), $sqlstring);
        $sqlstring = str_replace('#EDITLANGID#', $this->getLangId(), $sqlstring);

        $sqlstring = preg_replace_callback('~#STARTPARAM\.([^#]+)#~', function ($match) {
            $pfad       = $match[1];
            $startParam = $this->getInterpreterValue("startparam");

            return isset($startParam[$pfad]) ? $startParam[$pfad] : "";
        }, $sqlstring);

        // other php replacements todo: implement better (santi)
        $sqlstring = $this->parseOther($sqlstring);

        $sqlstring = explode("#AJAXPARAM.", $sqlstring);
        for ($x = 1; $x < count($sqlstring); $x++) {
            $pfad          = substr($sqlstring[$x], 0, strpos($sqlstring[$x], "#"));
            $wert          = $_REQUEST['ajaxparam'][$pfad];
            $sqlstring[$x] = str_replace($pfad . '#', $wert, $sqlstring[$x]);
        }
        $sqlstring = implode('', $sqlstring);

        $sqlstring = explode("#SESSION.", $sqlstring);
        for ($x = 1; $x < count($sqlstring); $x++) {
            $pfad          = substr($sqlstring[$x], 0, strpos($sqlstring[$x], "#"));
            $wert          = $_SESSION["interpreter"]['interfromulardata'][$pfad];
            $sqlstring[$x] = str_replace($pfad . '#', $wert, $sqlstring[$x]);
        }

        $sqlstring = implode('', $sqlstring);

        //search for #SQL:....:SQL#
        if (strpos($sqlstring, "#SQL:") !== false) {
            $tmp = explode("#SQL:", $sqlstring);
            for ($x = 0; $x < count($tmp); $x++) {
                if (strpos($tmp[$x], ":SQL#") !== false) {
                    $tmp2    = explode(":SQL#", $tmp[$x]);
                    $tmp[$x] = $this->getScalar($tmp2[0]) . $tmp2[1];
                }
            }
            $sqlstring = implode('', $tmp);
        }

        foreach ($sqlParsers as $sqlParser) {
            $sqlstring = $sqlParser->parseSQLString_after($sqlstring, $param);
        }

        return $sqlstring;
    }

    public function getNavi()
    {
        $ini  = $this->getIni();
        $navi = $ini["navi"];

        return $navi ? : "NEW";
    }

    public function getLangColSuffix()
    {
        if ($this->langsuffix === null) {
            $suffix       = "";
            $languageedit = $this->getLanguageEdit();
            if ($languageedit != "0" && is_numeric($languageedit)) {
                $suffix = "_" . $languageedit;
            }
            $this->langsuffix = $suffix;
        }

        return $this->langsuffix;
    }

    public function getLangId()
    {
        $lang         = 0;
        $languageedit = $this->getLanguageEdit();
        if (is_numeric($languageedit)) {
            $lang = $languageedit;
        }

        return $lang;
    }

    /**
     * tmp santi.
     * the idea is to be able to pass php information similar to #STARTPARAM.BLABLA#,
     * this is just me playing around with the options.
     */
    protected function parseOther($str)
    {
        return $str;
    }

    /**
     * correct path from myfolder/../out to out
     *
     * @param string $path
     *
     * @return string
     */
    public function realpath($path)
    {
        $startslash = false;
        if (substr($path, 0, 1) == "/") {
            $startslash = true;
            $path       = substr($path, 1);
        }

        $endslash = false;
        if (substr($path, strlen($path) - 1) == "/") {
            $endslash = true;
            $path     = substr($path, 0, strlen($path) - 1);
        }

        $tmp = explode("/", $path);

        $new    = [];
        $canpop = false;
        foreach ($tmp as $t) {
            if ($t == "..") {
                //remove the folderstring before this
                if ($canpop && count($new) > 0) {
                    array_pop($new);
                }
            } elseif ($t == ".") {
                //remove
            } else {
                array_push($new, $t);
                $canpop = true;
            }
        }

        $path = implode("/", $new);
        if ($startslash) {
            $path = "/" . $path;
        }
        if ($endslash) {
            $path = $path . "/";
        }

        return $path;
    }

    /**
     * @param string $name Name of the parameter
     * @param        $default
     *
     * @return array|string Value that is found in the Global Arrays
     *
     * 2016-08-04
     * change from static to public,
     * otherwise phpstorm do not display this method in the autocomplete box
     * i have not found a call that need static in the base project
     */
    public function getRequestParameter($name, $default = null)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        } elseif (isset($_GET[$name])) {
            return $_GET[$name];
        } elseif (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        } else {
            return $default;
        }
    }

    private function __clone()
    {
    }

}
