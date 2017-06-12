#! /usr/bin/env php
<?php
use de\mvo\Config;
use de\mvo\Database;
use de\mvo\Date;
use de\mvo\model\contacts\Contact;
use de\mvo\model\date\Entry;
use de\mvo\model\date\Groups;
use de\mvo\model\date\Location;
use de\mvo\model\forms\Form;
use de\mvo\model\messages\Message;
use de\mvo\model\notedirectory\Program;
use de\mvo\model\notedirectory\Title;
use de\mvo\model\notedirectory\Titles;
use de\mvo\model\permissions\Group;
use de\mvo\model\permissions\GroupList;
use de\mvo\model\permissions\Permissions;
use de\mvo\model\protocols\Protocol;
use de\mvo\model\roomoccupancyplan\Entry as RoomOccupancyPlanEntry;
use de\mvo\model\uploads\Upload;
use de\mvo\model\uploads\Uploads;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;

require_once __DIR__ . "/../bootstrap.php";

function migratePermissions($groups)
{
    $groupList = new GroupList;

    foreach ($groups as $group) {
        $users = new Users;

        if (isset($group->users)) {
            foreach ($group->users as $userId) {
                $user = User::getById($userId);
                if ($user === null) {
                    echo "Permissions: User " . $userId . " not found!\n";
                    continue;
                }

                $users->append($user);
            }
        }

        $groupObject = new Group;
        $groupObject->title = $group->title;
        $groupObject->permissions = new Permissions(isset($group->permissions) ? $group->permissions : array());
        $groupObject->users = $users;

        if (isset($group->subGroups)) {
            $groupObject->subGroups = migratePermissions($group->subGroups);
        }

        $groupList->append($groupObject);
    }

    return $groupList;
}

function migrateStage(PDO $oldDb, $stage)
{
    switch ($stage) {
        case "dates":
            echo "Migrating dates\n";

            $query = $oldDb->query("
                SELECT `startDate`, `endDate`, `title`, `description`, `locations`.`name` AS `location`, `groups`
                FROM `dates`
                LEFT JOIN `locations` ON `locations`.`id` = `locationId`
                WHERE `enabled`
            ");

            while ($row = $query->fetch()) {
                $location = Location::getByName($row->location);

                if ($location === null) {
                    $location = new Location;

                    $locationQuery = $oldDb->prepare("SELECT * FROM `locations` WHERE `id` = :id");

                    $locationQuery->execute(array
                    (
                        ":id" => $row->locationId
                    ));

                    $locationRow = $locationQuery->fetch();

                    $location->name = $locationRow->name;
                    $location->latitude = $locationRow->latitude;
                    $location->longitude = $locationRow->longitude;

                    $location->save();
                }

                $entry = new Entry;

                $entry->startDate = new Date($row->startDate);
                $entry->endDate = $row->endDate === null ? null : new Date($row->endDate);
                $entry->title = $row->title;
                $entry->description = $row->description;
                $entry->location = $location;
                $entry->highlight = $row->bold;

                $groups = new Groups;

                foreach (explode(",", $row->groups) as $group) {
                    if ($group === "public") {
                        $entry->isPublic = true;
                    } else {
                        $groups->append($group);
                    }
                }

                $entry->save();
            }
            break;

        case "forms":
            echo "Migrating forms\n";

            $query = $oldDb->query("SELECT * FROM `forms`");

            while ($row = $query->fetch()) {
                $form = new Form;

                $form->filename = $row->filename;
                $form->name = $row->name;
                $form->title = $row->title;

                $form->save();
            }
            break;

        case "users":
            echo "Migrating users\n";

            $contactsQuery = $oldDb->prepare("
                SELECT *
                FROM `phonenumbers`
                WHERE `userId` = :userId AND `category` IN ('phone', 'mobile') AND `subCategory` IN ('private', 'business')
            ");

            $query = $oldDb->query("SELECT * FROM `users`");

            while ($row = $query->fetch()) {
                $user = new User;

                $user->id = $row->id;
                $user->username = $row->username;
                $user->email = $row->email;
                // TODO: Migrate password
                $user->firstName = $row->firstName;
                $user->lastName = $row->lastName;
                $user->birthDate = new Date($row->birthDate);
                $user->lastOnline = $row->lastOnline === null ? null : new Date($row->lastOnline);
                $user->enabled = $row->enabled;

                $user->save(true);

                $contactsQuery->execute(array
                (
                    ":userId" => $user->id
                ));

                while ($contactsRow = $contactsQuery->fetch()) {
                    $contact = new Contact;

                    $contact->type = $contactsRow->category;
                    $contact->category = $contactsRow->subCategory;
                    $contact->user = $user;
                    $contact->value = $contactsRow->number;

                    $contact->save();
                }
            }

            $oldJson = json_decode(file_get_contents(Config::getRequiredValue("migrate", "path") . "/includes/permissions.json"));

            migratePermissions($oldJson)->save();
            break;

        case "messages":
            echo "Migrating messages\n";

            $messageRecipientsQuery = $oldDb->prepare("SELECT * FROM `messagetargets` WHERE `messageId` = :messageId");
            $messageFilesQuery = $oldDb->prepare("
                SELECT `uploads`.`name`, `uploads`.`title`
                FROM `messagefiles`
                LEFT JOIN `uploads` ON `uploads`.`id` = `messagefiles`.`fileId`
                WHERE `messageId` = :messageId
            ");

            $query = $oldDb->query("SELECT * FROM `messages` WHERE `enabled`");

            while ($row = $query->fetch()) {
                $message = new Message;

                $message->date = new Date($row->date);
                $message->sender = User::getById($row->userId);
                $message->text = $row->text;

                $message->recipients = new Users;

                $messageRecipientsQuery->execute(array
                (
                    ":messageId" => $row->id
                ));

                while ($recipientsRow = $messageRecipientsQuery->fetch()) {
                    $user = User::getById($recipientsRow->userId);
                    if ($user === null) {
                        echo "Message recipients: User " . $recipientsRow->userId . " not found!\n";
                        continue;
                    }

                    $message->recipients->append($user);
                }

                $message->attachments = new Uploads;

                $messageFilesQuery->execute(array
                (
                    ":messageId" => $row->id
                ));

                while ($filesRow = $messageFilesQuery->fetch()) {
                    $upload = new Upload;

                    $upload->key = $filesRow->key;
                    $upload->filename = $filesRow->title;

                    $upload->saveAsNew();

                    $message->attachments->append($upload);
                }

                $message->saveAsNew();
            }
            break;

        case "notedirectory":
            echo "Migrating note directory\n";
            $query = $oldDb->query("SELECT * FROM `notedirectory_titles`");

            while ($row = $query->fetch()) {
                $title = new Title;

                $title->id = $row->id;
                $title->title = $row->title;
                $title->composer = $row->composer;
                $title->arranger = $row->arranger;
                $title->publisher = $row->publisher;

                $title->save(true);
            }

            $programTitlesQuery = $oldDb->prepare("SELECT * FROM `notedirectory_programtitles` WHERE `programId` = :programId");

            $query = $oldDb->query("
                SELECT `notedirectory_programs`.`id`, `year`, `title` FROM `notedirectory_programs`
                LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId`
            ");

            while ($row = $query->fetch()) {
                $program = new Program;

                $program->title = $row->title;
                $program->year = $row->year;

                $program->generateName();

                $program->titles = new Titles;

                $programTitlesQuery->execute(array
                (
                    ":programId" => $row->id
                ));

                while ($titlesRow = $programTitlesQuery->fetch()) {
                    $title = Title::getById($titlesRow->titleId);

                    $title->number = $titlesRow->number;

                    $program->titles->append($title);
                }

                $program->save();
            }
            break;

        case "protocols":
            echo "Migrating protocols\n";

            $query = $oldDb->query("
                SELECT `protocols`.`name`, `protocols`.`date`, `protocols`.`groups`, `uploads`.`name` AS `key`, `uploads`.`filename`
                FROM `protocols`
                LEFT JOIN `uploads` ON `uploads`.`id` = `protocols`.`uploadId`
            ");

            while ($row = $query->fetch()) {
                $protocol = new Protocol;

                $protocol->title = $row->name;
                $protocol->date = new Date($row->date);
                $protocol->groups = new Groups(explode(",", $row->groups));

                $upload = new Upload;

                $upload->key = $row->key;
                $upload->filename = $row->filename;

                $upload->saveAsNew();

                $protocol->upload = $upload;

                $protocol->save();
            }
            break;

        case "roomoccupancyplan":
            echo "Migrating room occupancy plan\n";

            $query = $oldDb->query("SELECT * FROM `roomoccupancyplan`");

            while ($row = $query->fetch()) {
                $entry = new RoomOccupancyPlanEntry;

                $entry->title = $row->title;
                $entry->startTime = $row->startTime;
                $entry->endTime = $row->endTime;
                $entry->date = new Date($row->date);
                $entry->repeatTillDate = $row->endRepeat === null ? null : new Date($row->endRepeat);
                $entry->repeatWeekly = $row->weekly;

                $entry->save();
            }
            break;
        default:
            echo "Invalid stage: " . $stage . "\n";
            break;
    }
}

Database::init();

$oldDb = new PDO(
    Config::getRequiredValue("migrate", "db-dsn"),
    Config::getValue("migrate", "db-username"),
    Config::getValue("migrate", "db-password")
);

$oldDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$oldDb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

$stages = $argv;
array_shift($stages);// Remove script path ($argv[0])

if (empty($stages)) {
    echo "Usage: " . $argv[0] . " <stage> [<stage> [...]]\n";
    echo "\n";
    echo "Stages: dates, forms, users, messages, notedirectory, protocols, roomoccupancyplan\n";
}

foreach ($stages as $stage) {
    migrateStage($oldDb, $stage);
}