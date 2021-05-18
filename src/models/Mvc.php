<?php

/**
 * @desc Auto Generation Prototype model
 */

namespace src\models;

use src\lib\Database;
use src\lib\Router;

class Mvc extends Database {

    /**
     * Constructor of the model
     */
    public function __construct($tableName = '', $dbName = '') {
        $db = !empty($dbName) ? $dbName : 'db';
        parent::__construct($GLOBALS['config'][$db]);
        $this->tableName = !empty($tableName) ? $tableName : "game_list";
        $this->assignAttrs();
    }

    /**
     * 
     * @return Array
     */
    public static function attrs() {
        return ["id", "player_id", "game_id", "service_code", "updated_at"];
    }

   /**
     * 
     * @param String $scenario
     * @return Array
     */
    public function _rules($scenario = 'save') {
        $_rules = [
            'save' => [
                'user_id' => ['required'],
                'price' => ['required', 'float'],
                'address_id' => ['required']
            ]
        ];
        return array_key_exists($scenario, $_rules) ? $_rules[$scenario] : [];
    }

    /**
     * 
     * @param Array $updateDt
     * @param Array $orgDt
     * @return Array
     */
    public function assignUpdateAttrs($updateDt, $orgDt) {
        $attrs = [];
        foreach ($this->attrs() as $attr) {
            $attrs[$attr] = (array_key_exists($attr, $updateDt)) ? $updateDt[$attr] : $orgDt[$attr];
        }
        return $attrs;
    }

    /**
     * 
     * @return Array
     */
    public function validate($data) {
        $valObj = new Validator();
        $valObj->validate($data, $this->_rules('save'));
        $errors = $valObj->error();

        return is_array($errors) ? $errors : [];
    }

    /**
     * 
     * @return $this
     */
    public function assignAttrs($attrs = []) {
        $isExternal = !empty($attrs);
        foreach (($isExternal ? $attrs : self::attrs()) as $eAttr => $attr) {
            $aAttr = $isExternal ? $eAttr : $attr;
            $this->{$aAttr} = $isExternal ? $attr : (Router::post($attr) !== "" ? Router::post($attr) : "");
        }
        return $this;
    }

    /**
     * 
     * @param INT $pk
     */
    public function findByPK($pk) {
        $dtAry = parent::findByPK($pk);
        foreach ($dtAry as $attr => $val) {
            $this->{$attr} = $val;
        }
        return $this;
    }
    
    
    /**
     * Sample Save Function
     * @return INTEGER
     */
    public function save() {
        $data = [
            'user_id' => $this->getUserID($this->user_id), 'txn_ref' => $this->txn_ref, 'txn_status' => $this->txn_status,
            'price' => (float) $this->price, 'judges' => $this->judges, 'okey' => Helper::uniqueID(), 'address_id' => $this->address_id,
            'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'updated_on' => date('Y-m-d H:i:s')
        ];
        if (!empty($this->oid)) {
            unset($data['okey']);
            unset($data['user_id']);
            return $this->updateOrder($this->oid, $data);
        }
        try {
            $this->query("INSERT INTO $this->tableName(" . implode(',', array_keys($data)) . ") "
                    . "VALUES (:" . implode(', :', array_keys($data)) . ")");
            foreach ($data as $param => $value) {
                $this->bind($param, $value);
            }
            $this->execute();
            $this->oid = $this->lastInsertId();
            $this->okey = $data['okey'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

}
