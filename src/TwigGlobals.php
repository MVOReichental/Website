<?php
namespace App;

use App\Entity\users\User;
use Symfony\Component\DependencyInjection\Container;

class TwigGlobals
{
    public function __construct(private readonly Container $container)
    {
    }

    public function now(): Date
    {
        return new Date;
    }

    public function getCurrentUser(): ?User
    {
        return User::getCurrent();
    }

    public function getCurrentPath(): string
    {
        return $_SERVER["PATH_INFO"] ?? "";
    }

    public function isInternal(): bool
    {
        return str_starts_with(ltrim($this->getCurrentPath(), "/"), "internal") and $this->getCurrentUser();
    }

    public function hasOriginUser(): bool
    {
        return isset($_SESSION["originUserId"]);
    }

    public function isActivePage(string $url): bool
    {
        $urlParts = explode("/", trim($url, "/"));
        $pathParts = explode("/", trim($this->getCurrentPath(), "/"));

        foreach ($urlParts as $index => $part) {
            if (!isset($pathParts[$index])) {
                return false;
            }

            if ($pathParts[$index] !== $part) {
                return false;
            }
        }

        return true;
    }
}