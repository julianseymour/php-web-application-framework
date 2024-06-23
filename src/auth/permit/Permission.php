<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Closure;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\claim;

class Permission extends Basic implements StaticPermissionGatewayInterface
{

	use NamedTrait;

	protected $denialClosure;

	protected $permittanceClosure;

	protected $minimumAccessLevel;

	public function __construct($name, $closure = null){
		parent::__construct();
		$this->setName($name);
		if(isset($closure)){
			$this->setPermittanceClosure($closure);
		}
	}

	public function setPermittanceClosure(?Closure $closure): ?Closure{
		$f = __METHOD__;
		if(!$closure instanceof Closure){
			Debug::error("{$f} closure is not a valid closure");
		}elseif($this->hasPermittanceClosure()){
			$this->release($this->permittanceClosure);
		}
		return $this->permittanceClosure = $this->claim($closure);
	}

	public function hasPermittanceClosure():bool{
		return isset($this->permittanceClosure) && $this->permittanceClosure instanceof Closure;
	}

	public function getPermittanceClosure(){
		$f = __METHOD__;
		if(!$this->hasPermittanceClosure()){
			Debug::error("{$f} permittance closure is undefined");
		}
		return $this->permittanceClosure;
	}

	public function hasMinimumAccessLevel():bool{
		return isset($this->minimumAccessLevel);
	}

	public function getMinimumAccessLevel(){
		$f = __METHOD__;
		if(!$this->hasMinimumAccessLevel()){
			Debug::error("{$f} minimum access level is undefined");
		}
		return $this->minimumAccessLevel;
	}

	public function setMinimumAccessLevel($level){
		$f = __METHOD__;
		if(!is_int($level)){
			Debug::error("{$f} minimum access level must be an integer");
		}elseif($this->hasMinimumAccessLevel()){
			$this->release($this->minimumAccessLevel);
		}
		return $this->minimumAccessLevel = $this->claim($level);
	}

	public function permit(UserData $user, object $object, ...$params): int{
		$f = __METHOD__;
		$closure = $this->getPermittanceClosure();
		$status = $closure($user, $object, ...$params);
		if(!is_int($status)){
			Debug::error("{$f} permittance closure must return an integer");
		}elseif($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} {$err}");
		}
		return $status;
	}

	public static function getPermissionStatic($name, $object){
		$class = static::class;
		return new $class($name);
	}

	public function setDenialClosure(?Closure $closure): ?Closure{
		$f = __METHOD__;
		if($this->hasDenialClosure()){
			$this->release($this->denialClosure);
		}
		if(!$closure instanceof Closure){
			Debug::error("{$f} closure must be an instanceof Closure");
		}
		return $this->denialClosure = $this->claim($closure);
	}

	public function hasDenialClosure():bool{
		return isset($this->denialClosure) && $this->denialClosure instanceof Closure;
	}

	public function getDenialClosure(){
		$f = __METHOD__;
		if(!$this->hasDenialClosure()){
			Debug::error("{$f} denial closure is undefined");
		}
		return $this->denialClosure;
	}

	protected function deny(UserData $user, object $object, ...$parameters){
		$f = __METHOD__;
		if($this->hasDenialClosure()){
			$r = $this->getDenialClosure();
			if(!$r instanceof Closure){
				Debug::error("{$f} denial closure must be a closure");
			}
			$status = $r($user, $object, ...$parameters);
			if(!is_int($status)){
				Debug::error("{$f} denial closure must return an integer");
			}elseif($status === SUCCESS){
				Debug::error("{$f} denial closure cannot return SUCCESS");
			}
			return $status;
		}
		return ERROR_FORBIDDEN;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->denialClosure, $deallocate);
		$this->release($this->minimumAccessLevel, $deallocate);
		$this->release($this->permittanceClosure, $deallocate);
	}
}
