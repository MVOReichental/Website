<?php
namespace App\Entity\date;

use ArrayObject;
use App\Date;
use App\Entity\users\User;

class DateList extends ArrayObject
{
    public function visibleForUser(User $user): DateList
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($entry->isPublic()) {
                $list->append($entry);
                continue;
            }

            foreach ($entry->getGroups() as $group) {
                if ($user->hasPermission("dates.view." . $group)) {
                    $list->append($entry);
                    break;
                }
            }
        }

        return $list;
    }

    public function publiclyVisible(): DateList
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($entry->isPublic()) {
                $list->append($entry);
            }
        }

        return $list;
    }

    public function startingAt(Date $date): DateList
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($entry->getEndDate() === null) {
                $endDate = clone $entry->getStartDate();
                $endDate->setTime(23, 59, 59);
            } else {
                $endDate = $entry->getEndDate();
            }

            if ($endDate >= $date) {
                $list->append($entry);
            }
        }

        return $list;
    }

    public function withYear(int $year): DateList
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ((int)$entry->getStartDate()->format("Y") === $year) {
                $list->append($entry);
            }
        }

        return $list;
    }

    public function getYears(): array
    {
        $years = array();

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            $years[] = (int)$entry->getStartDate()->format("Y");
        }

        return array_unique($years);
    }

    public function getInGroups(Groups $groups, bool $includePublic): DateList
    {
        $list = new self;

        /**
         * @var $entry Entry
         */
        foreach ($this as $entry) {
            if ($includePublic and $entry->isPublic()) {
                $list->append($entry);
                continue;
            }

            if (!$entry->getGroups()->isAnyIn($groups)) {
                continue;
            }

            $list->append($entry);
        }

        return $list;
    }
}