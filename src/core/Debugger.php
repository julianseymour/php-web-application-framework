<?php

namespace JulianSeymour\PHPWebApplicationFramework\core;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\use_case;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

class Debugger extends Debug{

	use FlagBearingTrait;

	protected $enforcedPrivateKey;

	protected $logged;

	public static function declareFlags(): array{
		return [
			"forbidGuest"
		];
	}

	public function hasEnforcedPrivateKey(): bool{
		return isset($this->enforcedPrivateKey) && is_string($this->enforcedPrivateKey) && ! empty($this->enforcedPrivateKey);
	}

	public function setEnforcedPrivateKey(?string $key): ?string{
		if($key === null) {
			unset($this->enforcedPrivateKey);
		}
		return $this->enforcedPrivateKey = $key;
	}

	public function getEnforcedPrivateKey(): string{
		$f = __METHOD__;
		if(!$this->hasEnforcedPrivateKey()) {
			Debug::error("{$f} enforced private key is undefined");
		}
		return $this->enforcedPrivateKey;
	}

	public function enforcePrivateKey(?string $key): Debugger{
		$f = __METHOD__;
		if(!$this->hasEnforcedPrivateKey()) {
			$this->setEnforcedPrivateKey($key);
			Debug::print("{$f} enforcing private key with hash " . sha1($key));
		}elseif($key !== $this->getEnforcedPrivateKey()) {
			Debug::error("{$f} illegal key with hash " . sha1($key));
		}
		return $this;
	}

	public function log(string $s): int{
		if(!is_array($this->logged)) {
			$this->logged = [];
		}
		array_push($this->logged, $s);
		return count($this->logged);
	}

	public function spew(): void{
		$f = __METHOD__;
		if(isset($this->logged) && is_array($this->logged) && ! empty($this->logged)) {
			foreach($this->logged as $i => $l) {
				Debug::print("#{$i}: {$l}");
			}
		}
		$string = "";
		if(app() != null && app()->hasUserData()) {
			$user = user();
			$uc = $user->getClass();
			$key = $user->hasIdentifierValue() ? $user->getIdentifierValue() : "[undefined]";
			$string .= "user is a {$uc} with key \"{$key}\". ";
		}else{
			$string .= "user data is undefined.";
		}
		if(app() != null && app()->hasUseCase()){
			$uc = use_case();
			$string .= "Use case is a ".$uc->getShortClass()." declared ".$uc->getDeclarationLine();
		}else{
			$string .= "No use case.";
		}
		Debug::print("{$f} {$string}");
	}
}
