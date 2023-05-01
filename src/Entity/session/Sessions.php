<?php
namespace App\Entity\session;

use ArrayObject;
use DateInterval;
use DateTime;
use Exception;
use App\Database;
use App\Date;

class Sessions extends ArrayObject
{
    /**
     * @return Sessions
     */
    public static function getAll()
    {
        $sessions = new self;

        $query = Database::query("SELECT * FROM `sessions`");

        while ($session = $query->fetchObject(Session::class)) {
            $sessions->append($session);
        }

        return $sessions;
    }

    /**
     * @throws Exception
     */
    public static function gc()
    {
        $now = new Date;

        $withoutUserOldestDate = clone $now;
        $withoutUserOldestDate->sub(new DateInterval("PT30M"));

        $withUserOldestDate = clone $now;
        $withUserOldestDate->sub(new DateInterval("P1W"));

        $sessions = self::getAll();

        $sessions->withoutUser()->olderThan($withoutUserOldestDate)->delete();
        $sessions->withUser()->olderThan($withUserOldestDate)->delete();
    }

    /**
     * @return Sessions
     */
    public function withoutUser()
    {
        $sessions = new self;

        /**
         * @var $session Session
         */
        foreach ($this as $session) {
            if (!$session->hasUser()) {
                $sessions->append($session);
            }
        }

        return $sessions;
    }

    /**
     * @return Sessions
     */
    public function withUser()
    {
        $sessions = new self;

        /**
         * @var $session Session
         */
        foreach ($this as $session) {
            if ($session->hasUser()) {
                $sessions->append($session);
            }
        }

        return $sessions;
    }

    /**
     * @param DateTime $date
     * @return Sessions
     */
    public function olderThan(DateTime $date)
    {
        $sessions = new self;

        /**
         * @var $session Session
         */
        foreach ($this as $session) {
            if ($session->date < $date) {
                $sessions->append($session);
            }
        }

        return $sessions;
    }

    public function delete()
    {
        /**
         * @var $session Session
         */
        foreach ($this as $session) {
            $session->delete();
        }
    }
}