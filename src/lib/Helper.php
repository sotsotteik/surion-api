<?php

namespace src\lib;

/**
 * To handle the safe and secure encryptions and Decryptions Example usage as follows
  $encKey = 'MIAW';
  $cipherMethod = 'aes-128-cfb'; //'aes-128-gcm';
  $secure = new Helper($encKey, $cipherMethod);
  $encryptedToken = $secure->encrypt('The quick brown fox jumps over the lazy dog.');

  $dec = new Helper($encKey, $cipherMethod);
  $decryptedToken = $dec->decrypt($encryptedToken);
 */
class Helper {

    /**
     *
     * @var String default cipher method if none supplied
     */
    protected $method = 'aes-128-ctr';

    /**
     *
     * @var String
     */
    private $key;

    /**
     * 
     * @return String
     */
    protected function iv_bytes() {
        return openssl_cipher_iv_length($this->method);
    }

    /**
     * 
     * @param type $key
     * @param type $method
     */
    public function __construct($key = FALSE, $method = FALSE) {
        if (!$key) {
            $key = php_uname(); // default encryption key if none supplied
        }
// convert ASCII keys to binary format
        $this->key = (ctype_print($key)) ? openssl_digest($key, 'SHA256', TRUE) : $key;

        if ($method) {
            if (in_array(strtolower($method), openssl_get_cipher_methods())) {
                $this->method = $method;
            } else {
                die(__METHOD__ . ": unrecognised cipher method: {$method}");
            }
        }
    }

    /**
     * 
     * @param String $data
     * @return String Encrypted
     */
    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes($this->iv_bytes());
        return bin2hex($iv) . openssl_encrypt($data, $this->method, $this->key, 0, $iv);
    }

    /**
     * 
     * @param String $data Encrypted Data
     * @return boolean | Decrypted Text
     */
    public function decrypt($data) {
        $iv_strlen = 2 * $this->iv_bytes();
        if (preg_match("/^(.{" . $iv_strlen . "})(.+)$/", $data, $regs)) {
            list(, $iv, $crypted_string) = $regs;
            if (ctype_xdigit($iv) && strlen($iv) % 2 == 0) {
                return openssl_decrypt($crypted_string, $this->method, $this->key, 0, hex2bin($iv));
            }
        }
        return FALSE; // failed to decrypt
    }

    /**
     * 
     * @param String $content
     * @param Boolean $doubleEncode
     * @return String encoded content
     */
    public static function encode($content, $doubleEncode = true) {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     * This is the opposite of [[encode()]].
     * @param string $content the content to be decoded
     * @return string the decoded content
     */
    public static function decode($content) {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Method to produce Unique ID - Alpha Numeric
     * @return String
     */
    public static function uniqueID($len = 8) {
        return strtoupper(substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $len));
    }

    /**
     * 
     * @param String $base64Code
     * @return Mixed
     */
    public static function uploadImgBB($base64Code) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IMG_BB_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "key=" . IMG_BB . "&image=$base64Code");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $bbOut = curl_exec($ch);
        curl_close($ch);
        $bbData = json_decode($bbOut, true);
        return $bbData['data']['id'];
    }

}
