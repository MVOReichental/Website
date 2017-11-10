<?php
namespace de\mvo\service;

use DateInterval;
use de\mvo\Date;
use de\mvo\model\messages\Messages;
use de\mvo\model\pictures\YearList;
use de\mvo\model\users\User;
use de\mvo\model\users\Users;
use de\mvo\TwigRenderer;

class InternalHome extends AbstractService
{
    public function get()
    {
        $currentUser = User::getCurrent();

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

        return TwigRenderer::render("home-internal", array
        (
            "user" => User::getCurrent(),
            "nextBirthdays" => array_slice(Users::getAll()->enabledUsers()->nextBirthdayBetween($nextBirthdayStart, $nextBirthdayEnd)->sortByNextBirthdays()->getArrayCopy(), 0, 5),
            "messages" => $latestMessage,
            "albums" => $albums->getVisibleToUser($currentUser)->slice(0, 3),
        ));
    }
}