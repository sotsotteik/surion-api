<?php

namespace src\models\secure;

use inc\Core;

/**
 * To validate input fields
 *
 * @author Johnson
 */
class Validator {

    /**
     *
     * @var Array 
     */
    private $_errors = [];

    /**
     * 
     * @param Array $dataIN
     * @param Array $rules
     * @param Array $labels
     */
    public function validate($dataIN, $rules = [], $labels = []) {

        foreach ($dataIN as $item => $itemVal) {
            if (key_exists($item, $rules)) {
                foreach ($rules[$item] as $rule => $ruleVal) {
                    if (is_int($rule)) {
                        $rule = $ruleVal;
                    }
                    switch ($rule) {
                        case 'required':
                            if (empty($itemVal) && $ruleVal) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' required');
                            }
                            break;

                        case 'minLen':
                            if (strlen($itemVal) < $ruleVal) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' should be minimum ' . $ruleVal . ' characters');
                            }
                            break;

                        case 'maxLen':
                            if (strlen($itemVal) > $ruleVal) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' should be maximum ' . $ruleVal . ' characters');
                            }
                            break;

                        case 'numeric':
                            if (!ctype_digit($itemVal) && $ruleVal) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' should be numeric');
                            }
                            break;

                        case 'float':
                            if (!filter_var($itemVal, FILTER_VALIDATE_FLOAT) && $ruleVal) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' should be Decimal Number');
                            }
                            break;

                        case 'alpha':
                            if (!ctype_alpha($itemVal) && $ruleVal) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' should be alphabetic characters');
                            }
                            break;

                        case 'pattern':
                            if (preg_match($ruleVal, $itemVal)) {
                                $this->addError($item, $this->getLabel($labels, $item) . ' Invalid Input Data');
                            }
                            break;

                        case 'email':
                            if (!filter_var($itemVal, FILTER_VALIDATE_EMAIL) && $ruleVal) {
                                $this->addError($item, Core::t('validator', 'email'));
                            }
                            break;

                        case 'ipv6':
                            if (!filter_var($itemVal, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $ruleVal) {
                                $this->addError($item, Core::t('validator', 'ipv6'));
                            }
                            break;

                        case 'ipv4':
                            if (!filter_var($itemVal, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $ruleVal) {
                                $this->addError($item, Core::t('validator', 'ipv4'));
                            }
                            break;

                        case 'url':
                            if (!(filter_var($itemVal, FILTER_VALIDATE_URL) !== false) && $ruleVal) {
                                $this->addError($item, Core::t('validator', 'url'));
                            }
                            break;

                        case 'between':
                            if (count($ruleVal) === 2 && (($ruleVal[0] >= $itemVal) || ($itemVal > $ruleVal[1]))) {
                                $this->addError($item, Core::t('validator', 'between', ['min' => $ruleVal[0], 'max' => $ruleVal[1]]));
                            }
                            break;

                        case 'json':
                            json_decode($itemVal);
                            if (json_last_error() !== JSON_ERROR_NONE && $ruleVal) {
                                $this->addError($item, Core::t('validator', 'json'));
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * 
     * @param Array $labels
     * @param String $attr
     * @return String
     */
    private function getLabel($labels, $attr) {
        return '<strong>' . (array_key_exists($attr, $labels) ? $labels[$attr] : ucwords(str_replace('_', ' ', $attr))) . '</strong>';
    }

    /**
     * 
     * @param String $item
     * @param String $error
     */
    private function addError($item, $error) {
        $this->_errors[$item][] = $error;
    }

    /**
     * 
     * @return Mixed | Boolean | Array
     */
    public function error() {
        if (empty($this->_errors)) {
            return false;
        }
        return $this->_errors;
    }

}

/*
 *
 * Sample Testing Goes Here 
$data = ['username' => '', 'password' => 'pass'];
$rules = [
    'username' => ['required', 'minLen' => 6,'maxLen' => 150, 'alpha'],
    'password' => ['required', 'minLen' => 8]
];
$v = new Validator();
$v->validate($data, $rules);
if($v->error()){
    print_r($v->error());
} else{
    echo 'Ok';
}
 
 */