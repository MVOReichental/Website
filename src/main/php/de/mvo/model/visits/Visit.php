<?php
namespace de\mvo\model\visits;

use DateInterval;
use de\mvo\Config;
use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\users\User;
use de\mvo\utils\IPUtil;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Visit
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $ip;
    /**
     * @var Date
     */
    public $date;
    /**
     * @var Date
     */
    public $firstVisit;
    /**
     * @var Date
     */
    public $lastVisit;
    /**
     * @var User|null
     */
    public $user;
    /**
     * @var int|null
     */
    private $userId;

    public function __construct()
    {
        if ($this->id === null) {
            return;
        }

        $this->id = (int)$this->id;
        $this->date = new Date($this->date);

        $time = explode(":", $this->firstVisit);
        $this->firstVisit = clone $this->date;
        $this->firstVisit->setTime($time[0], $time[1], $time[2]);

        $time = explode(":", $this->lastVisit);
        $this->lastVisit = clone $this->date;
        $this->lastVisit->setTime($time[0], $time[1], $time[2]);

        if ($this->userId !== null) {
            $this->user = User::getById($this->userId);
        }
    }

    /**
     * @param string $ip
     * @param Date $date
     * @param User $user
     * @return Visit|null
     */
    public static function getByIpDateUser($ip, Date $date, User $user = null)
    {
        if ($user === null) {
            $query = Database::prepare("
                SELECT *
                FROM `visits`
                WHERE `ip` = :ip AND `date` = :date AND `userId` IS NULL
            ");

            $query->execute(array
            (
                ":ip" => $ip,
                ":date" => $date->toDatabaseDate()
            ));
        } else {
            $query = Database::prepare("
                SELECT *
                FROM `visits`
                WHERE `ip` = :ip AND `date` = :date AND `userId` = :userId
            ");

            $query->execute(array
            (
                ":ip" => $ip,
                ":date" => $date->toDatabaseDate(),
                ":userId" => $user->id
            ));
        }

        if (!$query->rowCount()) {
            return null;
        }

        return $query->fetchObject(self::class);
    }

    /**
     * @param Date $startDate
     * @param Date $endDate
     * @return Visit[]
     */
    public static function getInDateRange(Date $startDate, Date $endDate)
    {
        $query = Database::prepare("
            SELECT *
            FROM `visits`
            WHERE `date` BETWEEN :startDate AND :endDate
        ");

        $query->execute(array
        (
            ":startDate" => $startDate->toDatabase(),
            ":endDate" => $endDate->toDatabase()
        ));

        $visits = array();

        while ($visit = $query->fetchObject(self::class)) {
            $visits[] = $visit;
        }

        return $visits;
    }

    /**
     * @param Date $date
     * @return Visit[]
     */
    public static function getAtDate(Date $date)
    {
        $query = Database::prepare("
            SELECT *
            FROM `visits`
            WHERE `date` = :date
        ");

        $query->execute(array
        (
            ":date" => $date->toDatabaseDate()
        ));

        $visits = array();

        while ($visit = $query->fetchObject(self::class)) {
            $visits[] = $visit;
        }

        return $visits;
    }

    /**
     * @return Visit[]
     */
    public static function getCurrentVisits()
    {
        $date = new Date;

        $minTime = clone $date;
        $minTime->sub(new DateInterval("PT15M"));

        if ($minTime->format("Y-m-d") === $date->format("Y-m-d")) {
            $minTime = $minTime->format("H:i:s");
        } else {
            $minTime = "00:00:00";
        }

        $query = Database::prepare("
            SELECT *
            FROM `visits`
            WHERE `date` = :date AND `lastVisit` >= :minTime
        ");

        $query->execute(array
        (
            ":date" => $date->toDatabaseDate(),
            ":minTime" => $minTime
        ));

        $visits = array();

        while ($visit = $query->fetchObject(self::class)) {
            $visits[] = $visit;
        }

        return $visits;
    }

    public static function track()
    {
        if (!isset($_SERVER["HTTP_USER_AGENT"]) or preg_match("/^check_http/", $_SERVER["HTTP_USER_AGENT"])) {
            return;
        }

        $crawlerDetect = new CrawlerDetect;

        if ($crawlerDetect->isCrawler()) {
            return;
        }

        $ip = IPUtil::getClientIP(Config::getValue("visits", "trusted-proxies", []));
        $date = new Date;
        $user = User::getCurrent();

        $visit = Visit::getByIpDateUser($ip, $date, $user);

        if ($visit === null) {
            $visit = new Visit;

            $visit->ip = $ip;
            $visit->date = $date;
            $visit->user = $user;
            $visit->firstVisit = $date;
        }

        $visit->lastVisit = $date;

        $visit->save();
    }

    public function save()
    {
        if ($this->id === null) {
            $query = Database::prepare("
                INSERT INTO `visits`
                SET
                    `ip` = :ip,
                    `date` = :date,
                    `firstVisit` = :firstVisit,
                    `lastVisit` = :lastVisit,
                    `userId` = :userId
            ");

            $query->execute(array
            (
                ":ip" => $this->ip,
                ":date" => $this->date->toDatabaseDate(),
                ":firstVisit" => $this->firstVisit->toDatabaseTime(),
                ":lastVisit" => $this->lastVisit->toDatabaseTime(),
                ":userId" => $this->user === null ? null : $this->user->id
            ));

            $this->id = (int)Database::lastInsertId();
        } else {
            $query = Database::prepare("
                UPDATE `visits`
                SET
                    `ip` = :ip,
                    `date` = :date,
                    `firstVisit` = :firstVisit,
                    `lastVisit` = :lastVisit,
                    `userId` = :userId
                WHERE `id` = :id
            ");

            $query->execute(array
            (
                ":ip" => $this->ip,
                ":date" => $this->date->toDatabaseDate(),
                ":firstVisit" => $this->firstVisit->toDatabaseTime(),
                ":lastVisit" => $this->lastVisit->toDatabaseTime(),
                ":userId" => $this->user === null ? null : $this->user->id,
                ":id" => $this->id
            ));
        }
    }

    public static function removeIps(Date $beforeDate)
    {
        $query = Database::prepare("
            UPDATE `visits`
            SET `ip` = NULL
            WHERE `date` < :date
        ");

        $query->bindValue(":date", $beforeDate->toDatabaseDate());

        $query->execute();
    }

    public static function cleanup(Date $beforeDate)
    {
        $query = Database::prepare("
            DELETE FROM `visits`
            WHERE `date` < :date
        ");

        $query->bindValue(":date", $beforeDate->toDatabaseDate());

        $query->execute();
    }
}