<?php
namespace de\mvo\mail;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Queue
{
    public static function add(Message $message)
    {
        $filesystem = new Filesystem;

        $filesystem->dumpFile(sprintf("%s/%s", MAIL_QUEUE_ROOT, uniqid()), serialize($message));
    }

    public static function process()
    {
        if (!is_dir(MAIL_QUEUE_ROOT)) {
            return;
        }

        $finder = new Finder;

        $finder->files();
        $finder->in(MAIL_QUEUE_ROOT);

        $sender = null;

        foreach ($finder as $file) {
            $message = unserialize($file->getContents());

            unlink($file->getPathname());

            if (!($message instanceof Message)) {
                fwrite(STDERR, sprintf("Content of %s can not be unserialized to instance of Message class!", $file->getFilename()));
                continue;
            }

            if ($sender === null) {
                $sender = new Sender;
            }

            $sender->send($message);
        }
    }
}