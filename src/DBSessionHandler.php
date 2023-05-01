<?php
namespace App;

use SessionHandlerInterface;
use App\Entity\session\Session;
use App\Entity\users\User;

class DBSessionHandler implements SessionHandlerInterface
{
    public function close()
    {
        return true;
    }

    public function destroy($sessionId)
    {
        $session = Session::getById($sessionId);

        if ($session === null) {
            return false;
        }

        $session->delete();

        return true;
    }

    public function gc($maxLifeTime)
    {
        return true;
    }

    public function open($savePath, $name)
    {
        return true;
    }

    public function read($sessionId)
    {
        $session = Session::getById($sessionId);

        if ($session === null) {
            return "";
        }

        return $session->data;
    }

    public function write($sessionId, $data)
    {
        $session = new Session;

        $session->id = $sessionId;
        $session->date = new Date;
        $session->data = $data;
        $session->user = User::getCurrent();

        $session->save();

        return true;
    }

    public static function start()
    {
        $handler = new self;

        session_set_save_handler($handler);

        session_start();
    }
}