<?php

namespace PermissionsHandler;

use PermissionsHandler\Annotations\AbstractCheck;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Roles extends AbstractCheck
{
    public $roles;

    public $requireAll = false;

    public function check()
    {
        $user = $this->getUserFromGuards();

        if (! $user) {
            return false;
        }

        return $user->hasRole($this->roles, $this->requireAll);
    }
}
