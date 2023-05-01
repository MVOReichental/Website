<?php
namespace App\router;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use App\Controller\AbstractService;
use App\Controller\exception\LoginException;
use App\Controller\exception\PermissionViolationException;
use App\Entity\users\User;
use App\router\exception\TargetConfigurationException;

class Target
{
    /**
     * @var string
     */
    public $class;
    /**
     * @var string
     */
    public $method;
    /**
     * @var array
     */
    public $params;
    /**
     * @var array
     */
    public $methodArguments = array();
    /**
     * @var bool
     */
    public $requireLogin = false;
    /**
     * @var string
     */
    public $requiredPermission;

    public function className(string $class)
    {
        $this->class = $class;
        return $this;
    }

    public function method(string $method)
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

    public function permission(string $permission)
    {
        $this->requireLogin();
        $this->requiredPermission = $permission;
        return $this;
    }

    /**
     * @return mixed
     * @throws LoginException
     * @throws PermissionViolationException
     * @throws TargetConfigurationException
     * @throws ReflectionException
     */
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

            // Force user to change password if required
            if ($user->requirePasswordChange and !in_array($_SERVER["REQUEST_URI"], array("/internal/settings/account", "/internal/logout"))) {
                header("Location: /internal/settings/account");
                return "";
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