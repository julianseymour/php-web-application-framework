<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use function JulianSeymour\PHPWebApplicationFramework\array_remove_key;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Closure;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\account\activate\ActivateAccountUseCase;

trait PermissiveTrait{

	protected $permissions;

	protected $singlePermissionGateways;

	protected $permissionGateway;

	public function hasPermissionGateway(){
		return isset($this->permissionGateway);
	}

	public function setPermissionGateway($gateway){
		$f = __METHOD__;
		if (is_string($gateway)) {
			if (empty($gateway)) {
				Debug::error("{$f} empty string");
			} elseif (! class_exists($gateway)) {
				Debug::error("{$f} class \"{$gateway}\" does not exist");
			}
		}
		if (! is_a($gateway, StaticPermissionGatewayInterface::class, is_string($gateway))) {
			Debug::error("{$f} class is not a static permission gateway");
		}
		return $this->permissionGateway = $gateway;
	}

	public function getPermissionGateway(){
		$f = __METHOD__;
		if (! $this->hasPermissionGateway()) {
			Debug::error("{$f} permission gateway is undefined");
		}
		return $this->permissionGateway;
	}

	private static function validatePermissionGatewayClass($class){
		$f = __METHOD__;
		if (! is_string($class)) {
			Debug::error("{$f} permission gateway class is not a string");
			return false;
		} elseif (! class_exists($class)) {
			Debug::error("{$f} static permission gatewat class \"{$class}\" does not exist");
			return false;
		}
		return true;
	}

	public static function hasStaticPermissionGatewayClass(){
		$f = __METHOD__;
		if (! isset(static::$staticPermissionGatewayClass)) {
			// Debug::print("{$f} static permission gateway class is undefined");
			return false;
		} elseif (! static::validatePermissionGatewayClass(static::$staticPermissionGatewayClass)) {
			Debug::print("{$f} invalid permission gateway class");
			return false;
		}
		return true;
	}

	public static function getStaticPermissionGatewayClass(){
		$f = __METHOD__;
		if (! static::hasStaticPermissionGatewayClass()) {
			Debug::error("{$f} static permission gateway class is undefined or invalid");
		}
		return static::$staticPermissionGatewayClass;
	}

	public function hasPermission($name):bool{
		return isset($this->permissions) 
		&& is_array($this->permissions) 
		&& array_key_exists($name, $this->permissions);
	}

	public function setPermission($name, $closure){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		if ($closure instanceof Permission) {
			if($print){
				Debug::print("{$f} input parameter is a Permission");
			}
			$permission = $closure;
		} elseif (is_int($closure) || is_bool($closure)) {
			if($print){
				Debug::print("{$f} input parameter is an integer or boolean value");
			}
			$permission = $closure;
		} else {
			if($print){
				Debug::print("{$f} input parameter is something else");
			}
			$permission = new Permission($name, $closure);
		}
		if (! isset($this->permissions) || ! is_array($this->permissions)) {
			$this->permissions = [];
		}
		return $this->permissions[$name] = $permission;
	}

	public function hasSinglePermissionGateway($name):bool{
		return isset($this->singlePermissionGateways) && is_array($this->singlePermissionGateways) && array_key_exists($name, $this->singlePermissionGateways);
	}

	public function setSinglePermissionGateway($name, $gateway){
		$f = __METHOD__;
		if (is_string($gateway)) {
			if (! static::validatePermissionGatewayClass($gateway)) {
				Debug::error("{$f} invalid permission gateway class \"{$gateway}\"");
			}
		}
		if (! isset($this->singlePermissionGateways) || ! is_array($this->singlePermissionGateways)) {
			$this->singlePermissionGateways = [];
		}
		return $this->singlePermissionGateways[$name] = $gateway;
	}

	public function getSinglePermissionGateway($name){
		$f = __METHOD__;
		if (! $this->hasSinglePermissionGateway($name)) {
			Debug::error("{$f} this object does not have a single permission gateway for \"{$name}\"");
		}
		return $this->singlePermissionGateways[$name];
	}

	public final function getPermission(string $name){
		$f = __METHOD__;
		$print = false;
		if ($this->hasPermission($name)) { // 1. non-statically assigned permissions`
			if ($print) {
				Debug::print("{$f} permission \"{$name}\" is defined");
			}
			return $this->permissions[$name];
		} elseif ($this->hasSinglePermissionGateway($name)) { // 2. non-static single permission gateways
			if ($print) {
				Debug::print("{$f} single permission gateway is defined for \"{$name}\"");
			}
			$gateway = $this->getSinglePermissionGateway($name);
			if (is_string($gateway)) {
				if ($print) {
					Debug::print("{$f} returning single permission \"{$name}\" from static gateway class \"{$gateway}\"");
				}
				return $gateway::getPermissionStatic($name, $this);
			} elseif ($print) {
				Debug::print("{$f} returning permission from non-static gateway");
			}
			return $gateway->getPermission($name);
		} elseif ($this->hasPermissionGateway()) { // 3. non-static global permission gateway
			if ($print) {
				Debug::print("{$f} this object has a global permission gateway from permission \"{$name}\"");
			}
			$gateway = $this->getPermissionGateway();
			if (is_string($gateway)) {
				if ($print) {
					Debug::print("{$f} returning permission \"{$name}\" from global static gateway class \"{$gateway}\"");
				}
				return $gateway::getPermissionStatic($name, $this);
			} elseif ($gateway instanceof StaticPermissionGatewayInterface) {
				if ($print) {
					Debug::print("{$f} returning permission \"{$name}\" from global static gateway class \"{$gateway}\"");
				}
				return $gateway->getPermissionStatic($name, $this);
			} elseif ($print) {
				Debug::print("{$f} golbal permission gateway is permissive, but not a static permission gateway interface");
			}
			return $gateway->getPermission($name);
		} elseif ($this->hasStaticPermissionGatewayClass()) { // 4. static global permission gateway
			$spgc = $this->getStaticPermissionGatewayClass();
			if ($print) {
				Debug::print("{$f} static permission gateway class is defined as \"{$spgc}\"");
			}
			return $spgc::getPermissionStatic($name, $this);
		} elseif ($this instanceof StaticPermissionGatewayInterface) { // 5. take care of it with our own static function
			if ($print) {
				Debug::print("{$f} calling getPermissionStatic()");
			}
			return static::getPermissionStatic($name, $this);
		}
		Debug::warning("{$f} falling back on default permission (denied) for \"{$name}\"");
		return FAILURE;
	}

	public function removePermission($name){
		$f = __METHOD__;
		if (! $this->hasPermission($name)) {
			Debug::error("{$f} permission \"{$name}\" does not exist");
		}
		$this->permissions = array_remove_key($this->permissions, $name);
		if (empty($this->permissions)) {
			unset($this->permissions);
		}
		return $name;
	}

	public final function permit($user, $permission_name, ...$params){
		$f = __METHOD__;
		try {
			$print = false;
			$permission = $this->getPermission($permission_name);
			if ($permission === null) {
				Debug::error("{$f} permission object returned null");
				return FAILURE;
			} elseif (is_int($permission)) {
				if ($print) {
					$err = ErrorMessage::getResultMessage($permission);
					Debug::print("{$f} permission is the integer error code \"{$err}\"");
				}
				return $permission;
			} elseif (is_bool($permission)) {
				if ($print) {
					Debug::print("{$f} permission is a boolean value");
				}
				if ($permission) {
					if ($print) {
						Debug::print("{$f} access granted");
					}
					return SUCCESS;
				} elseif ($print) {
					Debug::print("{$f} access denied");
				}
				return FAILURE;
			}elseif(is_string($permission)){
				Debug::error("{$f} permission \"{$permission}\" is a string");
			} elseif ($print) {
				$pc = $permission->getClass();
				Debug::print("{$f} about to return {$pc}->permit()");
			}
			if (! isset($params)) {
				$params = [];
			}
			if ($permission instanceof Closure) {
				if ($print) {
					Debug::print("{$f} permission is a closure");
				}
				return $permission($user, $this, ...$params);
			} elseif ($print) {
				Debug::print("{$f} about to call permit() on permission \"{$permission_name}\"");
			}
			$status = $permission->permit($user, $this, ...$params);
			if ($status !== SUCCESS) {
				if ($print) {
					Debug::printStackTraceNoExit("{$f} permission \"{$permission_name}\" denied");
				}
			} elseif ($print) {
				Debug::print("{$f} permission \"{$permission_name}\" granted");
			}
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
