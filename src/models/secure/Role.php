<?php

namespace src\models\secure;

/**
 * Definition of Roles for the Admin
 *
 * @author Johnson<johnson@rigelsoft.com>
 */
class Role {

    const SA = 1; //Super Admin
    const STF = 2; //Staff

    /**
     * 
     * @param Array $roles
     * @return boolean
     */

    public static function hasAccess($roles = []) {
        if (!empty($roles)) {
            return (isset($_SESSION['loginAuth']) && !empty($_SESSION['loginAuth']) && in_array((int) $_SESSION['loginAuth']->role, $roles));
        }
        return false;
    }

}
