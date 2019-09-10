<?php

if ( !function_exists( 'globalShutdown' ) ) {
    function globalShutdown() {
        //release all db connections
        if ( isset( $GLOBALS['aMysqliConnections'] ) ) {
            /**
             * @var \mysqli $mysqli
             */
            foreach ( $GLOBALS['aMysqliConnections'] as $key => $mysqli ) {
                @$mysqli->close();
                $mysqli = null;
            }
        }
    }
}
register_shutdown_function( 'globalShutdown' );


/**
 * Class cconfig3
 *
 * @property  $_liveDbHost
 * @property  $_liveDbUser;
 * @property  $_liveDbPwd ;
 * @property  $_liveDbName;
 */
class hsdb {
    // region Singleton



    /**
     * @var string
     */
    protected $dbHost = '';
    /**
     * @var int
     */
    protected $dbPort = 3306;
    /**
     * @var string
     */
    protected $dbName = '';
    /**
     * @var string
     */
    protected $dbUser = '';
    /**
     * @var string
     */
    protected $dbPwd = '';
    /**
     * @var int
     */
    public $iUtfMode = 0;

    protected $reconnect = null;

    //TR 2015-05-20 add feature affected rows
    protected $_affectedrows = null;

    /**
     * @return string
     */
    public function getDbHost() {
        return $this->dbHost;
    }

    /**
     * @return string
     */
    public function getDbPort() {
        return $this->dbPort;
    }

    /**
     * @return string
     */
    public function getDbName() {
        return $this->dbName;
    }

    /**
     * @return string
     */
    public function getDbUser() {
        return $this->dbUser;
    }

    /**
     * @return string
     */
    public function getDbPwd() {
        return $this->dbPwd;
    }

    /**
     * @return bool
     */
    public function getDbUtf8() {
        return ($this->iUtfMode == "1" ? true : false);
    }


    protected $_originalkey = "";

    protected function _getOriginalKey() {
        return $this->_originalkey;
    }

    protected function _getCurrentKey() {
        return md5( serialize( $this->getConnectionData() ) );
    }

    public function __construct($connectionData) {
        $this->setConnectionData($connectionData);
        $this->_originalkey = $this->_getCurrentKey();
    }

    public function __destruct() {
    }




    //TR 2015-05-20 add feature affected rows

    /**
     * @return int
     */
    public function getAffectedRows() {
        return $this->_affectedrows;
    }


    /**
     * set connection data for the db
     * example:
     * $connectionData['dbHost'] = '123';
     * $connectionData['dbName'] = '123';
     * $connectionData['dbUser'] = '123';
     * $connectionData['dbPwd'] = '123';
     * $connectionData['iUtfMode'] = '123';
     *
     * @param array $connectionData
     */
    public function setConnectionData( $connectionData ) {
        if ( isset( $connectionData['dbHost'] ) ) {
            $this->dbHost = $connectionData['dbHost'];
        }
        if ( isset( $connectionData['dbPort'] ) ) {
            $this->dbPort = $connectionData['dbPort'];
        }
        if ( isset( $connectionData['dbName'] ) ) {
            $this->dbName = $connectionData['dbName'];
        }
        if ( isset( $connectionData['dbUser'] ) ) {
            $this->dbUser = $connectionData['dbUser'];
        }
        if ( isset( $connectionData['dbPwd'] ) ) {
            $this->dbPwd = $connectionData['dbPwd'];
        }
        if ( isset( $connectionData['iUtfMode'] ) ) {
            $this->iUtfMode = $connectionData['iUtfMode'];
        }

        $this->_originalkey = $this->_getCurrentKey();
    }


    /**
     * return database connection data as an array
     *
     * @return string[]
     */
    public function getConnectionData() {
        $connectionData             = [];
        $connectionData['dbHost']   = $this->dbHost;
        $connectionData['dbPort']   = $this->dbPort;
        $connectionData['dbName']   = $this->dbName;
        $connectionData['dbUser']   = $this->dbUser;
        $connectionData['dbPwd']    = $this->dbPwd;
        $connectionData['iUtfMode'] = $this->iUtfMode;

        return $connectionData;
    }


    protected function _getMysqliKey() {
        $key = $this->dbHost . "|" . $this->dbName . "|" . $this->dbUser . "|" . $this->dbPwd . "|" . $this->iUtfMode . '|' . $this->dbPort;
        $key = md5( $key );

        return $key;
    }

    /**
     * disconnects to the database
     **/
    public function disconnectToDb() {
        $key = $this->_getMysqliKey();
        if ( isset( $GLOBALS['aMysqliConnections'][$key] ) ) {
            /**
             * @var \mysqli $mysqli
             */
            $mysqli = null;
            $mysqli = $GLOBALS['aMysqliConnections'][$key];
            unset( $GLOBALS['aMysqliConnections'][$key] );

            if ( $mysqli !== null ) {
                @$mysqli->close();
                $mysqli = null;
            }
        }
    }


    /**
     * @return \mysqli|null
     */
    public function getDbId() {
        return $this->connectToDb();
    }


    /**
     * connects to the database with the gifen settings
     *
     * @return \mysqli
     **/
    protected function connectToDb() {
        /**  @var \mysqli $mysqli */
        $mysqli = null;
        $key    = $this->_getMysqliKey();
        if ( isset( $GLOBALS['aMysqliConnections'][$key] ) ) {
            $mysqli = $GLOBALS['aMysqliConnections'][$key];
        }

        if ( $mysqli == null || is_object( $mysqli ) == false ) {

            unset( $GLOBALS['aMysqliConnections'][$key] );
            $mysqli                              = new mysqli( $this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName, $this->dbPort );
            $GLOBALS['aMysqliConnections'][$key] = $mysqli;

            if ( $mysqli->connect_errno ) {
                throw(new Exception( 'Connect Error: ' . $mysqli->connect_errno . ' ' . $mysqli->connect_error, $mysqli->connect_errno ));
            }
            if ( $this->iUtfMode == 1 ) {
                if ( !$mysqli->set_charset( "utf8" ) ) {
                    throw(new Exception( "Error loading character set utf8: " . $mysqli->error, $mysqli->errno ));
                }
            }

            $mysqli->query("SET SESSION group_concat_max_len = 64000");

            //set global variables for logging (use in the trigger to detect if a user make changes)
            if ( $this->_getOriginalKey() == $this->_getCurrentKey() ) {
                try {
                    /**
                     * @var \mysqli        $mysqli
                     * @var \mysqli_result $rs
                     */
                    /*
                    $sql = "show tables like 'embaseuser'";
                    $rs  = $mysqli->query( $sql );
                    if ( $rs->num_rows > 0 ) {
                        require_once __DIR__ . "/../inc/functions.php";
                        require_once __DIR__ . "/../core/embaseuser.php";
                        $f_embaseuser = "";
                        if ( $oBaseUser = new \core\embaseuser( $this, null ) ) {
                            if ( $oBaseUser->loadFromHtaccessLogin() ) {
                                $f_embaseuser = $oBaseUser->getId();
                            }
                        }

                        $sql = "SET @f_embaseuser = '" . $f_embaseuser . "'";
                        $mysqli->query( $sql );
                    }
                    @$rs->close();
                    */

                } catch (Exception $e) {
                    //not connected to base, but maybe to the shop.
                    //this can happen, because formedit can connect to the shop
                    //or a remove call from base. In both version the base user table isn´t present
                    //in the database.
                }

            }

        }

        return $mysqli;
    }

    /**
     * execute a sql statment and returns the mysqli result
     * if the connection to the db is not connected, the connection gets established automatically
     * if the connection is disconnected, the function tries 3 times to establish the connection
     *
     * @param      $sqlstring
     * @param int  $iRetries           = how ofter should try if a error occur.
     * @param bool $closeOnZeroResults should the connection to the db be closed if there are zero results
     * @return bool|mysqli_result|null
     */
    public function execute( $sqlstring, $iRetries = 1, bool $closeOnZeroResults = true ) {
        //BASE-1802
        //$startTime = microtime( true );

        //TR 2015-05-20 add feature affected rows
        $this->_affectedrows = null;
        $rs                  = null;
        $mysqli              = null;

        try {
            $mysqli = $this->connectToDb();
            $rs     = $mysqli->query( $sqlstring );

            //TR 2015-05-20 add feature affected rows
            $this->_affectedrows = $mysqli->affected_rows;

            if ( $rs ) {
                if ( is_object( $rs ) == false || ($rs->num_rows == 0 && $closeOnZeroResults) ) {
                    $this->close( $rs );
                    $rs = null;
                }
            }
        } catch (Exception $exception) {
            return $this->_executeErrorHandling( $mysqli, $sqlstring, $iRetries );
        }


        return $rs;
    }

    /**
     * @param mysqli $mysqli
     *
     * @return bool|mysqli_result|null
     */
    protected function _executeErrorHandling( $mysqli, $sqlstring, $iRetries ) {
        if ( $iRetries > 0 ) {
            $this->disconnectToDb();
            $iRetries--;
            return $this->execute( $sqlstring, $iRetries );
        } else {
            //If is not a connect error, add the error to the stack of errors and send the error to NewRelic.
            $e                     = new mysqli_sql_exception( $mysqli->error, $mysqli->errno );
            $error                 = [];
            $error['exception']    = $e;
            $error['sqlstatment']  = $sqlstring;
            $error['mysql']        = $mysqli->errno;
            $this->_mysqli_error[] = $error;

            echo '<pre>';
            print_r($error);
            die("");
            //Sentry::captureException($e, ['query' => $sqlstring, 'retry' => $iRetries]);
        }
    }

    /**
     * Converts a resultset to a dataset with a fetch_object
     *
     * @param mysqli_result|boolean $rs
     *
     * @param bool                  $fetchObject if false, the fetch gonna by as array
     *
     * @return array
     */
    public function fetchRows( $rs, $fetchObject = true ) {
        $dataset = [];

        if ( $rs ) {
            while ( $row = $fetchObject ? $rs->fetch_object() : $rs->fetch_assoc() ) {
                $dataset[] = $row;
            }
        }

        return $dataset;
    }


    /**
     * execute a sqlstatment and returns only the first value on row 1, column 1
     * usefull if you want to get only one value like the number of rows form a table
     *
     * @param string $sqlstring
     *
     * @return string|null
     */
    public function getScalar( $sqlstring ) {
        //        self::log($sqlstring);

        $one = null;
        $rs  = $this->execute( $sqlstring );
        if ( $rs && $rs->num_rows > 0 ) {
            $row = $rs->fetch_row();
            $one = is_array( $row ) ? reset( $row ) : null;
            $this->close( $rs );
        }

        return $one;
    }

    /**
     * compatiblity reasons for the oxid shop
     *
     * @param $sqlstring
     *
     * @return string|null
     */
    public function getOne( $sqlstring ) {
        return $this->getScalar( $sqlstring );
    }


    /**
     * execute the insert, update, create, alter, delete statment
     *
     * @param string $sqlstring
     *
     * @return int affected rows
     */
    public function executeNoReturn( $sqlstring ) {
        $rs = $this->execute( $sqlstring );
        $af = $this->getAffectedRows();
        $this->close( $rs );

        return $af;
    }

    /**
     * execute a sqlstatment and retuns only the first row.
     * example: select * from oxarticles limit 0,1
     *
     * @param string $sqlstring
     * @param bool   $AsObject
     *
     * @return mixed|null|object|stdClass
     */
    public function getRow( $sqlstring, $AsObject = true ) {
        $row = null;
        $rs  = $this->execute( $sqlstring );
        if ( $rs && $rs->num_rows > 0 ) {
            try {
                if ( $AsObject ) {
                    $row = $rs->fetch_object();
                } else {
                    //MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH
                    $row = $rs->fetch_array( MYSQLI_ASSOC );
                }

                $this->close( $rs );
            } catch (Exception $e) {
                $error['exception']    = $e;
                $error['sqlstatment']  = $sqlstring;
                $this->_mysqli_error[] = $error;
            }
        }

        return $row;
    }

    /**
     * Get all the rows as array of a query
     *
     * @param $sql
     *
     * @return array
     */
    public function getRows( $sql ) {
        $rs = $this->execute( $sql );

        return $this->fetchRows( $rs );
    }



    /**
     * close a recordset
     *
     * @param mysqli_result $rs
     */
    public function close( $rs ) {
        if ( $rs && is_object( $rs ) ) {
            @$rs->close();
        }
    }

    /**
     * escapes a string and mark special characters like '
     *
     * @param $string
     *
     * @return string
     */
    public function escapeString( $string ) {
        $mysqli = $this->connectToDb();

        if ( $mysqli ) {
            $string = $mysqli->real_escape_string( $string );
        }

        return $string;
    }

    /**
     * @var null|array
     */
    protected $_mysqli_error = null;

    /**
     * get the last mysqli error
     * example:
     * $error['exception']=$e;
     * $error['sqlstatment']=$sqlstring;
     * $error['mysql']=$mysqli->errno;
     *
     * @return null|array
     */
    public function mysqlLastError() {
        return $this->_mysqli_error;
    }

    /**
     * clear the last error messages
     **/
    public function mysqlClearLastError() {
        $this->_mysqli_error = null;
    }


    protected $_max_allowed_packet = null;

    /**
     * if you want to send many sqlstatments separated with ; you can reach the allowed packet size.
     * here you can ask for the current packet size
     **/
    public function getMaxAllowdPacket() {

        if ( $this->_max_allowed_packet === null ) {
            $sql = "SHOW VARIABLES LIKE 'max_allowed_packet'";

            $rs = $this->execute( $sql );
            if ( $rs ) {
                $row                       = $rs->fetch_object();
                $this->_max_allowed_packet = $row->Value;
            }
            $this->close( $rs );
        }

        return $this->_max_allowed_packet;
    }


}

