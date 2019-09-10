<?php
/**
 * Developer:   Diego Miguel Angel López Muñoz
 * Date:        27/07/2017
 * Time:        05:22 PM
 */

namespace formedit\formedit\core;


use core\utilities\log;

class Controller
{
    protected $_hsConfig;
    protected $_cl, $_fnc;

    public function __construct()
    {
        $this->_hsConfig = getHsConfig();

        $this->_cl  = $this->getParam('cl');
        $this->_fnc = $this->getParam('fnc');
    }

    /**
     * Return a param escaped sent through a request
     *
     * @param      $param
     * @param null $default
     *
     * @return null
     */
    public function getParam($param, $default = null)
    {
        if (isset($_REQUEST[$param])) {
            return $this->getHsConfig()->escapeString($_REQUEST[$param]);
        }

        return $default;
    }

    /**
     * Retrieves the config on formedit
     *
     * @return \hsconfig|null
     */
    protected function getHsConfig()
    {
        return $this->_hsConfig;
    }

    /**
     * @param array|\mysqli_result $data
     * @param string               $filename
     */
    protected function responseToFile($data, $filename)
    {
        $oCsv = new \cexportcsv();
        $oCsv->setSeparator(",");
        $oCsv->setForceDownload(true);
        $oCsv->setFilename($filename);

        if ($data instanceof \mysqli_result) {
            $oCsv->exportFrom_mysqli($data);
        } elseif (is_array($data)) {
            $oCsv->exportFrom_array($data);
        }else{
            header("Content-Type: application/force-download");
            header("Content-Transfer-Encoding: binary");
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            fwrite(fopen('php://output', 'w'), 'No results');
        }
    }

    /**
     * Creates a FileName for the downloadable
     *
     * @param string $baseName
     * @param string $extension
     *
     * @return mixed
     */
    protected function getFileName($baseName = "", $extension = "txt")
    {
        $fileName = $baseName . " " . str_replace('get', '', $this->_fnc);

        if ($extension !== "txt") {
            $fileName .= ".$extension";
        }

        return trim($fileName);
    }

    public function index()
    {
        return "Not implemented";
    }
}