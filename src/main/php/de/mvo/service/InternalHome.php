<?php
namespace de\mvo\service;

use DateInterval;
use de\mvo\Date;
use de\mvo\model\date\DateList;
use de\mvo\model\messages\Messages;
use de\mvo\model\pictures\YearList;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\model\videos\VideoList;
use de\mvo\TwigRenderer;
use Exception;
use Twig_Error;

class InternalHome extends AbstractService
{
    /**
     * @return string
     * @throws Twig_Error
     * @throws Exception
     */
    public function get()
    {
        $currentUser = User::getCurrent();

        if (file_exists(News::INTERNAL_NEWS_FILE)) {
            $newsContent = file_get_contents(News::INTERNAL_NEWS_FILE);
        } else {
            $newsContent = null;
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

        $nextDatesStart = new Date;
        $nextDatesStart->setTime(0, 0);
        $nextDatesEnd = clone $nextDatesStart;
        $nextDatesEnd->add(new DateInterval("P4W"));

        $nextBirthdayStart = new Date;
        $nextBirthdayStart->setTime(0, 0);
        $nextBirthdayEnd = clone $nextBirthdayStart;
        $nextBirthdayEnd->add(new DateInterval("P4W"));

        return TwigRenderer::render("home-internal", array
        (
            "user" => User::getCurrent(),
            "news" => $newsContent,
            "nextDates" => array_slice(DateList::getBetween($nextDatesStart, $nextDatesEnd)->visibleForUser($currentUser)->getArrayCopy(), 0, 5),
            "nextBirthdays" => array_slice(Users::getAll()->enabledUsers()->nextBirthdayBetween($nextBirthdayStart, $nextBirthdayEnd)->sortByNextBirthdays()->getArrayCopy(), 0, 5),
            "messages" => $latestMessage,
            "albums" => $albums->getVisibleToUser($currentUser)->slice(0, 3),
            "picturesBaseUrl" => "internal/pictures",
            "videos" => VideoList::load()->sortByDate(false, true)->slice(0, 3)
        ));
    }
}