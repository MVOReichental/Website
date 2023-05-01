<?php
namespace App\router;

use AltoRouter;
use Exception;

class Router extends AltoRouter
{
    /**
     * @param $method
     * @param $route
     * @param Target $target
     * @throws Exception
     */
    public function mapTarget(string $method, string $route, Target $target)
    {
        parent::map($method, $route, $target);
    }

    /**
     * @param Endpoint[] $endpoints
     * @throws Exception
     */
    public function mapAll(array $endpoints)
    {
        /**
         * @var $endpoint Endpoint
         */
        foreach ($endpoints as $endpoint) {
            $this->mapTarget($endpoint->method, $endpoint->path, $endpoint->target);
        }
    }

    /**
     * @param string|null $requestUrl
     * @param string|null $requestMethod
     * @return Target|null
     */
    public function getMatchingTarget(string $requestUrl = null, string $requestMethod = null)
    {
        $match = $this->match($requestUrl, $requestMethod);
        if ($match === false) {
            return null;
        }

        /**
         * @var $target Target
         */
        $target = $match["target"];

        $target->params = (object)$match["params"];

        return $target;
    }
}