<?php

/**
 * @desc Generator View to generate the model
 */
$attrs = [];
$tbl = preg_replace_callback('/(?:^|_)([a-z])/', 
    function ($m) {
        return strtoupper($m[1]);
    }, $model->tableName);
$header = <<<EOT
<?php
/**
* @author Jay
* @desc <CoreGen>Auto Generated model
*/

namespace src\models;

use src\lib\Database;
use inc\Core;
use src\lib\Router;

/**

EOT;

foreach ($data as $dt) {
    $header .= "* @property " . $dt['Type'] . " $" . $dt['Field'] . "\n";
    $attrs[] = $dt['Field'];
}
$header .= '**/
    ';

$content = '
class ' . $tbl . ' extends Database {

     /**
     *
     * @var String
     */
    protected $pk = "id";
        
    /**
     * Constructor of the model
     */
    public function __construct($db = "db") {
        parent::__construct($GLOBALS[\'config\'][$db]);
        $this->tableName = "' . $model->tableName . '";
        $this->assignAttrs();
    }

    /**
     * 
     * @return Array
     */
    public static function attrs() {
        return ["' . implode('","', $attrs) . '"];
    }
';
$footer = '
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

}';
$overAll = $header . $content . $footer;
header('Content-Disposition: attachment; filename="' . $tbl . '.php"');
header('Content-Type: text/plain');
header('Content-Length: ' . strlen($overAll));
header('Connection: close');
echo $overAll;
?>