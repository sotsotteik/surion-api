<?php

namespace src\controllers;

use inc\Controller;
use src\lib\CliCaptcha;
use src\lib\Router;
use src\lib\Secure;
use src\models\secure\Validator;

/**
 * Core Index file as Landing Page
 */
class LoginController extends Controller {

    private $salt = 'sl-cd-abcd-cp-tkn';
    private $secretKey = "clicaptcha";

    /**
     * Action to test the Index
     * @return Mixed
     */
    public function actionIndex() {

        $sec = new Secure($this->salt);
        
        $secTokenForm = explode("_", $sec->decrypt(Router::post('sec')));

        $secTokenSession = explode("_", $sec->decrypt($_SESSION['clicaptcha']));

        if ($secTokenForm[1] == $this->secretKey && $secTokenForm[1] == $secTokenSession[1]) {

            // Start check credentials logic
            echo "Login success";
        }

    }
}
