<?php
namespace App\router;

class Endpoint
{
    /**
     * @var string
     */
    public $method;
    /**
     * @var string
     */
    public $path;
    /**
     * @var Target
     */
    public $target;

    /**
     * @param string $method
     * @param string $path
     */
    public function __construct(string $method, string $path)
    {
        $this->method = $method;
        $this->path = $path;
        $this->target = new Target;

        Endpoints::add($this);
    }

    /**
     * @return Target
     */
    public function target()
    {
        return $this->target;
    }

    /**
     * @param string $method
     * @param string $path
     * @return Endpoint
     */
    public static function create($method, $path)
    {
        return new self($method, $path);
    }
}