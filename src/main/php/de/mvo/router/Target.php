<?php
namespace de\mvo\router;

use de\mvo\model\users\User;
use de\mvo\router\exception\TargetConfigurationException;
use de\mvo\service\AbstractService;
use de\mvo\service\exception\LoginException;
use de\mvo\service\exception\PermissionViolationException;
use ReflectionClass;
use ReflectionMethod;

class Target
{
    public $class;
    public $method;
    public $params;
    public $methodArguments = array();
    public $requireLogin = false;
    public $requiredPermission;

    public static function create()
    {
        return new self;
    }

    public function className($class)
    {
        $this->class = $class;
        return $this;
    }

    public function method($method)
    {
        $this->method = $method;
        return $this;
    }

    public function arguments()
    {
        $this->methodArguments = func_get_args();
        return $this;
    }

    public function requireLogin()
    {
        $this->requireLogin = true;
        return $this;
    }

    public function permission($permission)
    {
        $this->requireLogin();
        $this->requiredPermission = $permission;
        return $this;
    }

    public function call()
    {
        if ($this->requireLogin) {
            $user = User::getCurrent();
            if ($user === null) {
                throw new LoginException(LoginException::NOT_LOGGED_IN);
            }

            if ($this->requiredPermission !== null and !$user->hasPermission($this->requiredPermission)) {
                throw new PermissionViolationException($this->requiredPermission);
            }
        }

        $reflectionClass = new ReflectionClass($this->class);

        if (!$reflectionClass->isInstantiable()) {
            throw new TargetConfigurationException("Class '" . $this->class . "' is not instantiable");
        }

        if (!$reflectionClass->isSubclassOf(AbstractService::class)) {
            throw new TargetConfigurationException("Class '" . $this->class . "' must be a subclass of '" . AbstractService::class . "'");
        }

        if (!$reflectionClass->hasMethod($this->method)) {
            throw new TargetConfigurationException("Method '" . $this->method . "' not found in class '" . $this->class . "'");
        }

        /**
         * @var $instance AbstractService
         */
        $instance = $reflectionClass->newInstance();

        $reflectionMethod = new ReflectionMethod($instance, $this->method);

        if (!$reflectionMethod->isPublic()) {
            throw new TargetConfigurationException("Method '" . $this->method . "' in class '" . $this->class . "' is not public");
        }

        $instance->params = $this->params;

        return $reflectionMethod->invokeArgs($instance, $this->methodArguments);
    }
}