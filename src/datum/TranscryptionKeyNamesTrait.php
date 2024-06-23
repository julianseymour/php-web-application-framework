<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TranscryptionKeynamesTrait{
	
	/**
	 * Name of a virtual column used to get the key used to decrypt this column's cipher value
	 *
	 * @var string
	 */
	protected $decryptionKeyName;
	
	/**
	 * name of the neighbor column that contains the key for en/decrypting this one
	 *
	 * @var string
	 */
	protected $transcryptionKeyName;
	
	public function hasDecryptionKeyName():bool{
		return isset($this->decryptionKeyName);
	}
	
	public function setDecryptionKeyName(?string $n):?string{
		$f = __METHOD__;
		if(!is_string($n)){
			Debug::error("{$f }transcryption key name must be a string");
			return null;
		}elseif(empty($n)){
			Debug::error("{$f} transcryption key name cannot be the empty string");
			return null;
		}elseif($this->hasDecryptionKeyName()){
			$this->release($this->decryptionKeyName);
		}
		return $this->decryptionKeyName = $this->claim($n);
	}
	
	public function getDecryptionKeyName():string{
		$f = __METHOD__;
		if(!$this->hasDecryptionKeyName()){
			$name = $this->getName();
			Debug::error("{$f} transcryption key name is undefined for column \"{$name}\"");
			return null;
		}
		return $this->decryptionKeyName;
	}
	
	public function hasTranscryptionKeyName():bool{
		return isset($this->transcryptionKeyName);
	}
	
	public function setTranscryptionKeyName(?string $n):?string{
		$f = __METHOD__;
		if(!is_string($n)){
			Debug::error("{$f }transcryption key name must be a string");
			return null;
		}elseif(empty($n)){
			Debug::error("{$f} transcryption key name cannot be the empty string");
			return null;
		}elseif($this->hasTranscryptionKeyName()){
			$this->release($this->transcryptionKeyName);
		}
		return $this->transcryptionKeyName = $this->claim($n);
	}
	
	public function getTranscryptionKeyName(){
		$f = __METHOD__;
		if(!$this->hasTranscryptionKeyName()){
			$name = $this->getName();
			Debug::error("{$f} transcryption key name is undefined for column \"{$name}\"");
			return null;
		}
		return $this->transcryptionKeyName;
	}
}