<?php

namespace inc;

/**
 * Core MVC Core
 *
 */
class Core {

    /**
     *
     * @var String ISO 639-1 Language Code 
     */
    public $lang = '';

    /**
     *
     * @var String
     */
    private $baseUrl = '';

    /**
     *
     * @var String
     */
    public $controllerPath = '\src\controllers\\';

    /**
     * Initial Functions related 
     */
    public function initApp() {
        $this->initSession();
        $this->siteLang();
        $this->baseUrl = parse_url(BASEURL);
        $this->parseURI();
    }

    /**
     * Method to initiate the session 
     */
    public function initSession() {
        ini_set("session.cookie_httponly", 1);
        session_start();
        ob_start();
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
    }

    /**
     * 
     * @param String $forceLang
     * @return String the Language
     */
    public function siteLang($forceLang = '') {
        $this->lang = (isset($_SESSION['lang'])) ? $_SESSION['lang'] : 'en';
        if (trim($forceLang) != '') {
            $this->lang = $forceLang;
        }
        $_COOKIE['language'] = $this->lang;
        $_SESSION['lang'] = $this->lang;
        setlocale(LC_ALL, $this->lang);
    }

    /**
     * 
     * @throws \Exception
     */
    private function parseURI() {
        $req = $_SERVER['REQUEST_URI'];
        $reqUrl = $this->extractURL($req);
        $ctrlCount = $this->baseUrl['path'] !== '/' ? 2 : 1;

        try {
            if (count($reqUrl) >= $ctrlCount && trim($reqUrl[1]) !== '') {
                $ctrlName = array_key_exists('0', $reqUrl) ? ucfirst($reqUrl[0]) : 'Index';
                $ctlr = $this->controllerPath . $ctrlName . 'Controller';
                $ctlrIns = new $ctlr();
                return $this->parseAction($ctlrIns, array_slice($reqUrl, 1), strtolower($ctrlName));
            } else {
                return $this->renderJSON(['status' => 'error', 'msg' => 'Controller Not Found! ===> ' . $ex->getMessage()], 404);
            }
        } catch (\Exception $ex) {
            return $this->renderJSON(['status' => 'error', 'msg' => 'Action Not Found! ===> ' . $ex->getMessage()], 404);
        }
    }

    /**
     * 
     * @param RequestString $req
     * @return String
     */
    private function extractURL($req) {
        $isHostExists = strpos($req, array_key_exists('host', $this->baseUrl) && $this->baseUrl['host'] ? $this->baseUrl['host'] : '');
        if ($isHostExists !== false) {
            $req = str_replace($this->baseUrl['host'], '', str_replace('https://', '', str_replace('http://', '', $req)));
        }
        $reqPath = str_replace(array_key_exists('path', $this->baseUrl) && $this->baseUrl['path'] !== '/' ? $this->baseUrl['path'] : '', '', $req);
        $mvcPath = explode('?', ltrim($reqPath, '/'));
        $reqURI = explode('/', $mvcPath[0]);
        $reqUrl = array_filter($reqURI, function($val) {
            return ($val !== NULL && $val !== FALSE && $val !== '');
        });
        $params = $GLOBALS['config'];
        if ((count($reqUrl) === 1 || count($reqUrl) === 0 ) && count($params) > 0 && array_key_exists('mvc', $params) && array_key_exists('defaults', $params['mvc']) && array_key_exists('controller', $params['mvc']['defaults'])) {
            $reqUrl[0] = (count($reqUrl) === 1) ? $reqUrl[0] : $params['mvc']['defaults']['controller'];
            $reqUrl[1] = array_key_exists('action', $params['mvc']['defaults']) ? $params['mvc']['defaults']['action'] : 'index';
        }
        $this->prettyURL($reqUrl);
        return $reqUrl;
    }

    /**
     * To enable user friendly Pretty URL
     * @param Array $reqUrl
     */
    private function prettyURL($reqUrl) {
        $params = $GLOBALS['config'];
        if (count($params) > 0 && array_key_exists('route', $params) && array_key_exists('prettyURL', $params['route']) && $params['route']['prettyURL'] === true) {
            if (count($reqUrl) > 2) {
                unset($reqUrl[0]);
                unset($reqUrl[1]);
            }
            $GLOBALS['routes'] = [];
            $rChunks = array_chunk($reqUrl, 2);
            foreach ($rChunks as $k => $r) {
                if (count($r) === 2) {
                    $GLOBALS['routes'][$r[0]] = $r[1];
                } elseif (count($r) === 1) {
                    $GLOBALS['routes'][$r[0]] = '';
                }
            }
        }
    }

    /**
     * 
     * @param Object $ctlr
     * @param Array $uri
     * @return Mixed
     * @throws \Exception
     */
    public function parseAction($ctlr, $uri, $ctlrName = '') {
        if (is_object($ctlr) && $ctlr->reqSent === true) {
            return;
        }
        if (!empty($uri) && trim($uri[0]) && is_object($ctlr)) {
            $action = 'action';
            $acCase = $action . ucfirst($uri[0]);
            $ctlr->checkAccess($ctlrName, strtolower($uri[0]));
            return $ctlr->$acCase();
        } else {
            return $this->renderJSON(['status' => 'error', 'msg' => 'Action Not Found!'], 404);
        }
    }

    /**
     * 
     * @param Array $results
     * @param Integer $statusCode HTTP Response Code
     */
    private function renderJSON($results, $statusCode = 200) {
        header("Content-Type: application/json");
        $json = json_encode($results);
        http_response_code($statusCode);
        echo $json;
    }

    /**
     * 
     * @param String $cat
     * @param String $key
     * @param Array $params
     * @return type
     * @throws \Exception
     */
    public static function t($cat = 'app', $key = '', $params = []) {
        $langPath = BASEPATH . '/src/i18n/' . $_SESSION['lang'] . '/' . $cat . '.php';
        if (file_exists($langPath)) {
            $msgs = include $langPath;
            if (array_key_exists($key, $msgs)) {
                $trans = $msgs[$key];
                if (count($params) > 0) {
                    foreach ($params as $pKey => $pVal) {
                        $trans = str_replace('{{' . $pKey . '}}', $pVal, $trans);
                    }
                }
                return $trans;
            } else {
                return $key;
            }
        } else {
            $this->renderJSON(['status' => 'error', 'msg' => 'Language Category File ' . $cat . ' Not Found!'], 404);
        }
    }

}
