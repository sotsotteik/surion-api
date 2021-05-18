<?php

namespace src\lib;

/**
 * Main Router File to handle the GET, POST, JSON
 */
class Router {

    /**
     * 
     * @param String $queryString
     * @return String
     */
    public static function get($queryString = '') {
        //Check for Pretty URL
        $params = $GLOBALS['config'];
        $get = (array_key_exists('route', $params) && array_key_exists('prettyURL', $params['route']) && $params['route']['prettyURL'] === true) ?
                $GLOBALS['routes'] : $_GET;

        return isset($get[$queryString]) ? self::cleanMe($get[$queryString]) : '';
    }

    /**
     * 
     * @param String $queryString
     * @return String
     */
    public static function post($queryString = '') {
        return isset($_POST[$queryString]) ? self::cleanMe($_POST[$queryString]) : '';
    }

    /**
     * 
     * @param String $queryString
     * @return String
     */
    public static function req($queryString = '') {
        return isset($_REQUEST[$queryString]) ? self::cleanMe($_REQUEST[$queryString]) : '';
    }

    /**
     * Method to return the all GET method values
     * @return type
     */
    public static function getAll() {
        $params = $GLOBALS['config'];
        //Check Pretty URL
        $get = (array_key_exists('route', $params) && array_key_exists('prettyURL', $params['route']) && $params['route']['prettyURL'] === true) ?
                $GLOBALS['routes'] : $_GET;
        return self::cleanAllValue($get);
    }

    /**
     * Method to return the all POST method values
     * @return type
     */
    public static function postAll() {
        return self::cleanAllValue($_POST);
    }

    /**
     * 
     * @param String $queryString
     * @return String
     */
    public static function reqAll() {
        return self::cleanAllValue($_REQUEST);
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    public static function cleanAllValue($data) {
        $newArray = [];
        foreach ($data as $key => $val) {
            $newArray[$key] = is_array($val) ? self::cleanAllValue($val) : self::cleanMe($val);
        }
        return $newArray;
    }

    /**
     * Function to return the data that are received by OPTION or POST without WWW-
     * @return Array
     */
    public static function getINPost($key) {
        $postdata = file_get_contents("php://input");
        $res = json_decode($postdata, true);
        return json_last_error() === JSON_ERROR_NONE && array_key_exists($key, $res) ? $res[$key] : null;
    }

    /**
     * Function to return the data that are received by OPTION or POST without WWW-
     * @return Array
     */
    public static function getINAllPost() {
        $postdata = file_get_contents("php://input");
        $res = json_decode($postdata, true);
        return json_last_error() === JSON_ERROR_NONE ? $res : [];
    }

    /**
     * 
     * @return String
     */
    public static function getReqMethod() {
        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '';
    }

    /**
     * 
     * @return Boolean
     */
    public static function isAjaxReq() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * 
     * @param String $url
     * @return Mixed
     */
    public static function cGet($url, $isRaw = false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . self::getHash()
        ));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
//                throw new Exception(curl_error($ch));
            $result = null;
        }
        curl_close($ch);
        return $result !== null ? (($isRaw === true) ? $result : self::rJSON($result)) : $result;
    }

    /**
     * 
     * @param String $res
     * @return Mixed
     */
    public static function rJSON($res) {
        $response = json_decode($res, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $response : $res;
    }

    /**
     * 
     * @param Array $data
     * @param String $url
     * @return Mixed
     */
    public static function cPost($data = [], $url = '', $isRaw = false) {
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'Authorization: ' . self::getHash()
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return ($isRaw === true) ? $result : self::rJSON($result);
    }

    /**
     * 
     * @return Hashed String 
     */
    public static function getHash() {
        $password = $GLOBALS['config']['authKey'];
        return password_hash(
                base64_encode(
                        hash('sha256', $password, true)
                ), PASSWORD_DEFAULT
        );
    }

    /**
     * 
     * @param String $input
     * @return Mixed
     */
    public static function cleanMe($input) {
        if (is_array($input)) {
            $newInput = [];
            foreach ($input as $key => $value) {
                $inputStiped = stripslashes($value);
                $inputHtml = htmlspecialchars($inputStiped, ENT_IGNORE, 'utf-8');
                $newInput[$key] = strip_tags($inputHtml);
            }
            return $newInput;
        } else {
            $inputStiped = stripslashes($input);
            $inputHtml = htmlspecialchars($inputStiped, ENT_IGNORE, 'utf-8');
            return strip_tags($inputHtml);
        }
    }

}
