<?php
namespace App\Entity\date;

use App\DBAL\Types\DateGroupsType;
use App\DBAL\Types\DateType;
use App\Repository\DateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Eluceo\iCal\Component\Event;
use App\Date;

#[ORM\Entity(repositoryClass: DateRepository::class)]
#[ORM\Table(name: "dates")]
class Entry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(name: "startDate", type: "datetime")]
    private Date $startDate;

    #[ORM\Column(name: "endDate", type: "datetime")]
    private ?Date $endDate = null;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\Column(type: Types::STRING)]
    private ?string $description = null;

    #[ORM\JoinColumn(name: "locationId")]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    private Location $location;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $highlight = false;

    #[ORM\Column(name: "isPublic", type: Types::BOOLEAN)]
    private bool $isPublic = false;

    #[ORM\Column(type: "dategroups")]
    private Groups $groups;

    public function __construct()
    {
        $this->groups = new Groups;
    }

    public function getIcalEvent(): Event
    {
        $event = new Event(sprintf("dates-%d@%s", $this->id, $_SERVER["SERVER_NAME"]));

        $event->setDtStart($this->startDate);
        $event->setDtEnd($this->endDate);
        $event->setUseUtc(false);
        $event->setUseTimezone(true);

        $event->setNoTime(!$this->startDate->hasTime());

        $event->setSummary($this->title);
        $event->setDescription($this->description);

        return $event;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStartDate(): Date
    {
        return $this->startDate;
    }

    public function setStartDate(Date $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?Date
    {
        return $this->endDate;
    }

    public function setEndDate(?Date $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function isHighlight(): bool
    {
        return $this->highlight;
    }

    public function setHighlight(bool $highlight): void
    {
        $this->highlight = $highlight;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function getGroups(): Groups
    {
        return $this->groups;
    }

    public function hasGroup(string $group): bool
    {
        return $this->groups->has($group);
    }

    public function addGroup(string $group): void
    {
        $this->groups->append($group);
    }
}