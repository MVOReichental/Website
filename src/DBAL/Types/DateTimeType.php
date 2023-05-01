<?php
namespace App\DBAL\Types;

use App\Date;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType as BaseDateTimeType;

class DateTimeType extends BaseDateTimeType
{
    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Date
    {
        if ($value === null) {
            return null;
        }

        return new Date($value);
    }
}