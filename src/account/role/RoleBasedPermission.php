<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\role;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\Permission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Closure;
use Exception;

class RoleBasedPermission extends Permission{

	protected $rolePolicies;

	// protected $weight;
	
	public function __construct(string $name, ?Closure $closure = null, ?array $policies = null){
		parent::__construct($name, $closure);
		if($policies !== null){
			$this->setRolePolicies($policies);
		}
	}

	public function setRolePolicies(?array $policies): ?array{
		if($this->hasRolePolicies()){
			$this->release($this->rolePolicies);
		}
		
		return $this->rolePolicies = $this->claim($policies);
	}

	public function hasRolePolicies():bool{
		return isset($this->rolePolicies);
	}

	public function getRolePolicies(){
		$f = __METHOD__;
		if(!$this->hasRolePolicies()){
			Debug::error("{$f} role policies are undefined for this ".$this->getDebugString());
		}
		return $this->rolePolicies;
	}

	public function permit(UserData $user, object $object, ...$parameters): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered");
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			$roles = $object->getUserRoles($mysqli, $user);
			$policies = $this->getRolePolicies();
			$permit = false;
			foreach($policies as $rolename => $p){
				if($print){
					Debug::print("{$f} considering role \"{$rolename}\"");
				}
				if(false !== array_search($rolename, $roles)){
					if(is_string($p)){
						if($print){
							Debug::print("{$f} policy \"{$p}\" is a string");
						}
						switch($p){
							case POLICY_REQUIRE:
							case POLICY_ALLOW:
								if($print){
									Debug::print("{$f} role \"{$rolename}\" is required or allowed");
								}
								$permit = true;
								continue 2;
							case POLICY_BLOCK:
							default:
								return $this->deny($user, $object, ...$parameters);
						} // switch
					}elseif($p instanceof Closure){
						if($print){
							Debug::print("{$f} policy is a closure");
						}
						$status = $p($user, $object, ...$parameters);
						if($status === SUCCESS){
							if($print){
								Debug::print("{$f} closure returned success");
							}
							$permit = true;
							continue;
						}elseif($print){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} permittance closure returned error status \"{$err}\"");
						}
						return $status;
					}elseif(is_int($p)){
						if($print){
							Debug::print("{$f} policy {$p} is an integer");
						}
						if($p === SUCCESS){
							if($print){
								Debug::print("{$f} access granted");
							}
							$permit = true;
							continue;
						}elseif($print){
							$err = ErrorMessage::getResultMessage($p);
							Debug::warning("{$f} policy is error status \"{$err}\"");
						}
						return $p;
					}else{
						$gottype = is_object($p) ? $p->getClass() : gettype($p);
						Debug::error("{$f} invalid policy type \"{$gottype}\"");
						return $this->deny($user, $object, ...$parameters);
					} // if is string
				}elseif(gettype($p) === gettype(POLICY_REQUIRE) && $p === POLICY_REQUIRE){
					if($print){
						Debug::print("{$f} role \"{$rolename}\" is required, but the user does not have it");
					}
					return $this->deny($user, $object, ...$parameters);
				} // if array search
			} // foreach
			if($permit){
				if($this->hasPermittanceClosure()){
					if($print){
						Debug::print("{$f} additional permittance closure must be satisified");
					}
					return parent::permit($user, $object, ...$parameters);
				}elseif($print){
					Debug::print("{$f} access granted");
				}
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} access denied");
			}
			return $this->deny($user, $object, ...$parameters);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->rolePolicies, $deallocate);
	}
}
