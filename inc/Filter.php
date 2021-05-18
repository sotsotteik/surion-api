<?php

namespace inc;

use src\lib\Role;

/**
 * Description of Filter
 *
 */
trait Filter {

    /**
     *
     * @var String
     */
    private $actn;

    /**
     *
     * @var String
     */
    private $ctlr;

    /**
     * 
     * @param Array $rules
     */
    public function accessControl($rules = []) {
        // Auth Users
        if ($this->isAuth() !== false) {
            if (array_key_exists('@', $rules)) {
                $this->authAccess($rules['@']);
            }
        } else {
            if (array_key_exists('*', $rules) && !in_array($this->actn, $rules['*'])) {
                echo $this->renderJSON(['status' => 'error', 'msg' => 'Oops! Unauthorized Request #ERRAUTH002'], 401); die;
            }
        }
    }

    /**
     * 
     * @param Array $rules
     * @return Boolean
     */
    private function authAccess($rules) {
        foreach ($rules as $rule) {
            $curAction = (array_key_exists('allow', $rule) && in_array($this->actn, $rule['allow']));
            $isRole = (array_key_exists('roles', $rule) && in_array((int) $this->isAuth(), $rule['roles']));
            if ($curAction === true && $isRole === true) {
                return true;
            }
        }
        echo $this->renderJSON(['status' => 'error', 'msg' => 'Oops! Unauthorized Request #ERRAUTH002'], 401); die;
    }

    /**
     * 
     * @return Mixed
     */
    public function isAuth() {
        if (isset($GLOBALS['loginAuth'])) {
            return $GLOBALS['loginAuth']->role;
        }
        return false;
    }

    /**
     * 
     * @param String $ctlr
     * @param String $action
     */
    public function setup($ctlr, $action) {
        $this->actn = $action;
        $this->ctlr = $ctlr;
    }

    /**
     * 
     * @param String $ctlr
     * @param String $action
     * @return boolean
     */
    public function checkAccess($ctlr, $action) {
        $access = Role::access();
        if (isset($GLOBALS['loginAuth'])) {
            if ($ctlr . '/' . $action === 'index/noaccess') {
                return true;
            }
            $role = $GLOBALS['loginAuth']->role;
            if (array_key_exists((int) $role, $access) && in_array($ctlr . '/' . $action, $access[$role])) {
                return true;
            } else {
                echo $this->renderJSON(['status' => 'error', 'msg' => 'Oops! Unauthorized Request #ERRAUTH002'], 401); die;
            }
        }
        return true;
    }

}
