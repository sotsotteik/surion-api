<?php

/**
 * Core controller File
 *
 */

namespace inc;

use src\lib\ArrayToXml;
use src\models\Login;

class Controller {

    /**
     *
     * @var boolean 
     */
    public $reqSent = false;

    /**
     *
     * @var String 
     */
    public $responseType = 'json';

    /**
     *
     * @var Array
     */
    public $params = [];

    /**
     *
     * @var String represents the layout file name 
     */
    public $layout = 'main';

    /**
     *
     * @var String, HTML Based
     */
    public $content = "";

    /**
     *
     * @var String
     */
    public $lang;

    /**
     *
     * @var String
     */
    public $controller = '';

    /**
     *
     * @var String
     */
    public $action = '';
    
    private $headers = [];
    
    public $title = '';

    /**
     * Method to verify the authorization and authentication
     */
    public function __construct() {
        $this->headers = apache_request_headers();
        $isDivert = $this->verifyAuth($this->headers);
        if ($isDivert !== true) {
            echo $isDivert;
            die;
        }
    }

    /**
     * 
     * @param Array $headers
     * @return Boolean Authentication
     */
    private function verifyAuth($headers) {
        $password = $GLOBALS['config']['authKey'];
        $retBool = false;
        if (array_key_exists('Authorization', $headers)) {
            if ($password === $headers['Authorization']) {
                $retBool = true;
            }
            if (array_key_exists('Api-Key', $headers) && Login::verifyAuth($headers['Api-Key'])) {
                $retBool = true;
            }
            return ($retBool === true) ? true :
                    $this->renderJSON(['status' => 'error', 'msg' => 'Oops! Unauthorized Request #ERRAUTH002'], 401);
        }
        return true;
    }

    /**
     * 
     * @param String $ctlr
     * @param String $action
     * @return boolean
     */
    public function checkAccess($ctlr, $action) {
        $this->controller = $ctlr;
        $this->action = $action;
        return true;
    }

    /**
     * 
     * @param Array $results
     * @param Integer $statusCode HTTP Response Code
     */
    public function render($results, $statusCode = 200) {
        if (strtolower($this->responseType) === 'json') {
            return $this->renderJSON($results, $statusCode);
        } else {
            return $this->renderXML($results, $statusCode);
        }
    }

    /**
     * 
     * @param Array $results
     * @param Integer $statusCode HTTP Response Code
     */
    public function renderJSON($results, $statusCode = 200) {
        header("Content-Type: application/json");
        $json = json_encode($results);
        http_response_code($statusCode);
        echo $json;
    }

    /**
     * 
     * @param Array $results
     * @param Integer $statusCode HTTP Response Code
     */
    public function renderXML($results, $statusCode = 200) {
        header("Content-type: text/xml; charset=utf-8");
        http_response_code($statusCode);
        echo ArrayToXml::convert($results);
    }

    /**
     * 
     * @param type $viewFile
     * @param type $params
     */
    public function renderUI($viewFile = '', $params = []) {
        $view = 'src/views/';
        $this->params = $params;
        $viewInclude = BASEPATH . $view . $viewFile . '.php';
        if (file_exists($viewInclude)) {
            $this->content = $this->renderPhpFile($viewInclude, $params);
        }

        include_once BASEPATH . $view . 'layouts/' . $this->layout . '.php';
    }

    /**
     * 
     * @param String $viewFile
     * @param String $params
     */
    public function renderPartial($viewFile = '', $params = []) {
        $view = 'src/views/';
        $this->params = $params;
        $viewInclude = BASEPATH . $view . $viewFile . '.php';
        if (file_exists($viewInclude)) {
            echo $this->renderPhpFile($viewInclude, $params);
        }
    }

    /**
     * @param string $_file_ the view file.
     * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return string the rendering result
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderPhpFile($_file_, $_params_ = []) {
        $_obInitialLevel_ = ob_get_level();
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        try {
            require $_file_;
            return ob_get_clean();
        } catch (\Exception $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            } throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $_obInitialLevel_) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            } throw $e;
        }
    }

    /**
     * Flush all once the request has completed
     */
    public function __destruct() {
        ob_flush();
    }

}
