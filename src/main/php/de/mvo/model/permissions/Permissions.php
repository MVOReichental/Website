<?php
namespace de\mvo\model\permissions;

use ArrayObject;
use JsonSerializable;

class Permissions extends ArrayObject implements JsonSerializable
{
    public function hasPermission($permission, $requireExactMatch = true)
    {
        foreach ($this as $thisPermission) {
            if ($thisPermission == $permission) {
                return true;
            }

            if ($requireExactMatch) {
                continue;
            }

            // The given permission matches a wildcard of this permission
            if (fnmatch($thisPermission, $permission)) {
                return true;
            }

            // This permission matches a wildcard of the given permission
            if (fnmatch($permission, $thisPermission)) {
                return true;
            }
        }

        return false;
    }

    public function addAll(Permissions $permissions)
    {
        foreach ($permissions as $permission) {
            $this->append($permission);
        }
    }

    public function makeUnique()
    {
        $this->exchangeArray(array_unique((array)$this));
    }

    function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}