<?php
namespace App\Entity\date;

use ArrayObject;

class Groups extends ArrayObject
{
    public function has(string $group): bool
    {
        return in_array($group, $this->getArrayCopy());
    }

    public function isAnyIn(Groups $groups): bool
    {
        foreach ($this as $group) {
            if ($groups->has($group)) {
                return true;
            }
        }

        return false;
    }

    public function __toString(): string
    {
        return implode(",", $this->getArrayCopy());
    }
}