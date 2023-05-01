<?php
namespace App\Controller;

use DateInterval;
use App\Date;
use App\Entity\date\DateList;
use App\Entity\messages\Messages;
use App\Entity\pictures\YearList;
use App\Entity\users\User;
use App\Entity\users\Users;
use App\Entity\videos\VideoList;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: "InternalHome::", methods: ["GET"])]
class InternalHome extends AbstractController
{
    #[Route("/internal")]
    #[Template("home-internal.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function get(): array
    {
        $currentUser = User::getCurrent();

        if (file_exists(News::INTERNAL_NEWS_FILE)) {
            $newsContent = file_get_contents(News::INTERNAL_NEWS_FILE);
            $newsDate = new Date;
            $newsDate->setTimestamp(filemtime(News::INTERNAL_NEWS_FILE));
        } else {
            $newsContent = null;
            $newsDate = null;
        }

        $receivedMessages = Messages::getByRecipient($currentUser, 1);
        $sentMessages = Messages::getBySender($currentUser, 1);

        if (!$receivedMessages->count()) {
            $latestMessage = $sentMessages;
        } elseif (!$sentMessages->count()) {
            $latestMessage = $receivedMessages;
        } elseif ($sentMessages->offsetGet(0)->id > $receivedMessages->offsetGet(0)->id) {
            $latestMessage = $sentMessages;
        } else {
            $latestMessage = $receivedMessages;
        }

        $albums = YearList::load()->getAllAlbums();

        $albums->sortByDate(false);

        $nextBirthdayStart = new Date;
        $nextBirthdayStart->setTime(0, 0);
        $nextBirthdayEnd = clone $nextBirthdayStart;
        $nextBirthdayEnd->add(new DateInterval("P4W"));

        return [
            "user" => User::getCurrent(),
            "news" => $newsContent,
            "newsDate" => $newsDate,
            "nextDates" => DateList::get(3),
            "nextBirthdays" => array_slice(Users::getAll()->enabledUsers()->nextBirthdayBetween($nextBirthdayStart, $nextBirthdayEnd)->sortByNextBirthdays()->getArrayCopy(), 0, 5),
            "messages" => $latestMessage,
            "albums" => $albums->getVisibleToUser($currentUser)->slice(0, 3),
            "picturesBaseUrl" => "internal/pictures",
            "videos" => VideoList::load()->sortByDate(false, true)->slice(0, 3)
        ];
    }
}