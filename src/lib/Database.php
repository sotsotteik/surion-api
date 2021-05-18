<?php

namespace src\lib;

/**
 * Core Database Resource Handler File
 *
 */
class Database {

    protected $dbh;
    protected $error;
    protected $stmt;
    public $tableName = '';

    /**
     * 
     * @param String $db
     */
    public function __construct($db) {
        $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'];
        $options = array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        );
        try {
            $this->dbh = new \PDO($dsn, $db['user'], $db['pass'], $options);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * 
     * @param String $query
     */
    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
    }

    /**
     * 
     * @param Array $param
     * @param String $value
     * @param Mixed $type
     */
    public function bind($param, $value, $type = null) {

        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_STR;
                    $value = '';
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }
        return $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {

        return $this->stmt->execute();
    }

    public function resultset() {
        $this->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    public function endTransaction() {
        return $this->dbh->commit();
    }

    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }

    public function debugDumpParams() {
        return $this->stmt->debugDumpParams();
    }

    public function getError() {
        return $this->error;
    }

    public function getValue() {
        $row = $this->single();
        return current($row);
    }

    public function getConnection() {
        return $this->dbh;
    }

    /**
     * Method to return the stripped alphanumeric
     * @param String $attr
     * @return String
     */
    public function stripAttr($attr) {
        return preg_replace("/[^a-zA-Z0-9]+/", "", $attr);
    }

    /**
     * 
     * @param Array $attrs
     * @return String
     */
    public function where($attrs = []) {
        $where = '';
        if (!empty($attrs)) {
            $cnt = 1;
            $where = ' WHERE ';
            foreach ($attrs as $attr => $val) {
                $or = (is_array($val) && array_key_exists('||', $val)) ? ' OR ' : ' AND ';
                $and = $cnt !== 1 ? $or : ' ';
                $where .= (is_array($val) && array_key_exists('||', $val)) ? $and . '`' . $attr . "` = :" . $this->stripAttr($attr) : (is_array($val) ? $and . $attr . " IN ( :id_" . implode(',:id_', array_keys($val)) . ")" : $and . '`' . $attr . "` = :" . $this->stripAttr($attr));
                $cnt++;
            }
        }
        return $where;
    }

    /**
     * 
     * @param Array $attrs
     */
    public function attrBind($attrs = []) {
        if (!empty($attrs)) {
            foreach ($attrs as $attr => $val) {
                if (is_array($val)) {
                    if (array_key_exists('||', $val)) {
                        $this->bind(':' . $this->stripAttr($attr), $val['||']);
                    } else {
                        foreach ($val as $k => $v) {
                            $this->bind(':id_' . $k, $v);
                        }
                        if (count($val) === 0) {
                            $this->bind(':id_', '');
                        }
                    }
                } else {
                    $this->bind(':' . $this->stripAttr($attr), $val);
                }
            }
        }
    }

    /**
     * 
     * @param Array $attrs
     * @return Array
     */
    public function findAll($attrs = [], $orderBy = '', $limit = '') {
        $where = $this->where($attrs);
        $order = ($orderBy !== '') ? ' ORDER BY ' . $orderBy : '';
        $query = "SELECT * FROM `$this->tableName` " . $where . ' ' . $order . ' ' . $limit;
        $this->query($query);
        $this->attrBind($attrs);
        return $this->resultset();
    }

    /**
     * 
     * @param Array $attrs
     * @return int
     */
    public function getCount($attrs = [], $orderBy = '', $limit = '') {
        $where = $this->where($attrs);
        $order = ($orderBy !== '') ? ' ORDER BY ' . $orderBy : '';
        $query = "SELECT count(1) as tot FROM `$this->tableName` " . $where . ' ' . $order . ' ' . $limit;
        $this->query($query);
        $this->attrBind($attrs);
        $data = $this->single();
        return ($data && !empty($data) && array_key_exists('tot', $data)) ? (int) $data['tot'] : 0;
    }

    /**
     * 
     * @return String
     */
    public function getPK() {
        $query = "SHOW KEYS FROM $this->tableName WHERE Key_name = 'PRIMARY'";
        $this->query($query);
        $dt = $this->single();
        return isset($dt['Column_name']) ? $dt['Column_name'] : '';
    }

    /**
     * 
     * @param INT $pk
     * @return Array
     */
    public function findByPK($pk) {
        $query = "SELECT * FROM $this->tableName WHERE " . $this->getPK() . "='$pk'";
        $this->query($query);
        return $this->single();
    }

    /**
     * 
     * @param Mixed $value
     * @return Mixed
     */
    public function getProcType(&$value) {
        switch (true) {
            case is_int($value):
                return \PDO::PARAM_INT;
            case is_bool($value):
                return \PDO::PARAM_BOOL;
            case is_null($value):
                $type = \PDO::PARAM_STR;
                $value = '';
                return $type;
            default:
                return \PDO::PARAM_STR;
        }
    }

    /**
     * 
     * @param String $type
     * @param Array $params
     * @return String
     */
    public function genExp($type = '', $params = [], $isSelect = false) {
        if (count($params) === 0) {
            return '';
        }
        if ($isSelect === true) {
            $process = [];
            foreach ($params as $param => $key) {
                $process[] = $type . $param . ' as ' . $param;
            }
            return implode(',', $process);
        } else {
            return $type . implode(',' . $type, array_keys($params));
        }
    }

    /**
     * 
     * @param String $proc
     * @param Array $inParams
     * @param Array $outParams
     * @return Mixed
     */
    public function callProc($proc = '', $inParams = [], $outParams = []) {
        $comma = count($inParams) > 0 && count($outParams) > 0 ? ',' : '';
        $sql = 'CALL ' . $proc . '(' . $this->genExp(':', $inParams) . $comma . $this->genExp('@', $outParams) . ')';
        $this->query($sql);
        foreach ($inParams as $param => $val) {
            $type = $this->getProcType($val);
            $this->stmt->bindParam(':' . $param, $val, $type);
        }
        $this->stmt->execute();
        $this->stmt->closeCursor();
        $this->query("SELECT " . $this->genExp('@', $outParams, true));
        return $this->single();
    }

    /**
     * 
     * @return Array
     */
    private function skipAttrs() {
        return [(string) $this->getPK(), 'created_at'];
    }

    /**
     * 
     * @return Array | Mixed | Null
     */
    private function getColumns() {
        $attrs = [];
        $skipAttr = $this->skipAttrs();
        $this->query('SHOW COLUMNS FROM ' . $this->tableName);
        foreach ($this->resultset() as $rec) {
            if (!in_array((string) $rec['Field'], $skipAttr)) {
                $attrs[$rec['Field']] = $this->{$rec['Field']};
            }
        }
        return $attrs;
    }

    /**
     * 
     * @return Mixed | Null | Integer
     */
    public function save() {
        $attr = $this->getColumns();
        $this->query('INSERT INTO ' . $this->tableName . '(`' . implode('`,`', array_keys($attr)) . '`) VALUES(:' . implode(',:', array_keys($attr)) . ')');
        foreach ($attr as $param => $value) {
            $this->bind($param, $value);
        }
        $this->execute();
        return $this->lastInsertId();
    }

    /**
     * 
     * @return Array
     */
    private function getValAttrs() {
        $attrs = [];
        foreach ($this->attrs()as $attr) {
            if (isset($this->{$attr}) && !empty($this->{$attr})) {
                $attrs[$attr] = $this->{$attr};
            }
        }
        return $attrs;
    }

    /**
     * 
     * @return Mixed | Null | Integer
     */
    public function update($where = []) {
        $attr = $this->getValAttrs();
        $setAttrs = [];
        $whereAttr = '';
        foreach ($attr as $k => $v) {
            $setAttrs[] = $k . ' = :' . $k;
        }
        if (!array_key_exists($this->getPK(), $where)) {
            $where[$this->getPK()] = $this->{$this->getPK()};
        }
        $whereAttr = $this->where($where);
        $this->query('UPDATE ' . $this->tableName . ' SET ' . implode(',', $setAttrs) . ' ' . $whereAttr);
        $this->attrBind($attr + $where);
        return $this->execute();
    }

}
