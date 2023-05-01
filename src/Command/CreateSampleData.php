<?php
namespace App\Command;

use App\Date;
use App\Entity\date\Entry;
use App\Entity\date\Location;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSampleData extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setName("create-sample-data");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        var_dump(Type::getTypesMap());

        $locations = [];

        $location = new Location;
        $location->setName("Schulhof / Festhalle");
        $location->setLatLon(48.7301, 8.39091);
        $this->entityManager->persist($location);
        $locations[] = $location;

        $location = new Location;
        $location->setName("Grüner Baum");
        $location->setLatLon(48.7298, 8.39214);
        $this->entityManager->persist($location);
        $locations[] = $location;

        $location = new Location;
        $location->setName("Gernsbach - Schloßstrasse");
        $location->setLatLon(48.7626, 8.33657);
        $this->entityManager->persist($location);
        $locations[] = $location;

        $entry = new Entry;
        $entry->setTitle("Some event");
        $entry->setStartDate(new Date("yesterday 14:00"));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(true);
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("Another event");
        $entry->setStartDate(new Date("tomorrow 12:00"));
        $entry->setEndDate(new Date("tomorrow 13:00"));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(true);
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("Foo");
        $entry->setStartDate(new Date("Next monday 18:00"));
        $entry->setEndDate(new Date("Next monday 20:00"));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(false);
        $entry->addGroup("x");
        $entry->addGroup("y");
        $entry->addGroup("z");
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("Bar");
        $entry->setStartDate(new Date(sprintf("%d-12-24 22:00", date("Y"))));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(true);
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("Baz");
        $entry->setStartDate(new Date(sprintf("%d-12-31 23:50", date("Y"))));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(true);
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("An event");
        $entry->setStartDate(new Date("+1 hour"));
        $entry->setEndDate(new Date("+2 hours"));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(true);
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("Some other event");
        $entry->setStartDate(new Date("-15 minutes"));
        $entry->setEndDate(new Date("+45 minutes"));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(true);
        $this->entityManager->persist($entry);

        $entry = new Entry;
        $entry->setTitle("My event");
        $entry->setStartDate(new Date("tomorrow"));
        $entry->setLocation($locations[array_rand($locations)]);
        $entry->setPublic(false);
        $this->entityManager->persist($entry);

        $this->entityManager->flush();

        return self::SUCCESS;
    }
}