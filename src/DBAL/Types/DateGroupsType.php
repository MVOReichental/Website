<?php
namespace App\DBAL\Types;

use App\Entity\date\Groups;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class DateGroupsType extends StringType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Groups
    {
        if ($value === null) {
            return new Groups;
        }

        $groups = new Groups;

        foreach (explode(",", $value) as $group) {
            $group = trim($group);
            if ($group === "") {
                continue;
            }

            $groups->append($group);
        }

        return $groups;
    }
}