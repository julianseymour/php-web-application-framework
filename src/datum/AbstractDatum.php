<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HumanReadableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * base class for Datum and DatumBundle
 *
 * @author j
 *        
 */
abstract class AbstractDatum extends Basic implements ArrayKeyProviderInterface{

	use AbstractColumnDefinitionTrait;
	use ArrayPropertyTrait;
	use HumanReadableNameTrait;

	/**
	 * Specify which group of embedded datums this is a part of
	 *
	 * @var string
	 */
	protected $embeddedName;

	/**
	 * EncryptionScheme class used by this datum to perform transcryption operations
	 *
	 * @var string
	 */
	protected $encryptionScheme;


	/**
	 * Where the value is stored for persistence.
	 * Not to be confused with Datum->databaseStorageType.
	 *
	 * @var int
	 */
	protected $persistenceMode;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			COLUMN_FILTER_EVENT_SOURCE,
			COLUMN_FILTER_NULLABLE
		]);
	}

	public function setEventSourceFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_EVENT_SOURCE, $value);
	}

	public function getEventSourceFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_EVENT_SOURCE);
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->defaultValue, $deallocate);
		$this->release($this->embeddedName, $deallocate);
		$this->release($this->encryptionScheme, $deallocate);
		$this->release($this->humanReadableName, $deallocate);
		$this->release($this->persistenceMode, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}

	public function embed(string $group_name){
		$f = __METHOD__;
		if($this->hasEmbeddedName()){
			$this->release($this->embeddedName);
		}
		if(!is_string($group_name)){
			Debug::error("{$f} group name must be a string");
		}elseif(empty($group_name)){
			Debug::error("{$f} group name cannot be empty");
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_EMBEDDED);
		$this->claim($group_name);
		$this->embeddedName = $group_name;
		return $this;
	}

	public function hasEmbeddedName():bool{
		return isset($this->embeddedName);
	}

	public function getEmbeddedName(){
		$f = __METHOD__;
		if(!$this->hasEmbeddedName()){
			$cn = $this->getName();
			Debug::error("{$f} embed group name is undefined for column \"{$cn}\"");
		}
		return $this->embeddedName;
	}

	/**
	 * where is the data stored -- nowhere, mysql database (default), session, cookies, local storage, indexed db, remote
	 *
	 * @return int
	 */
	public function getPersistenceMode(){
		if(!$this->hasPersistenceMode()){
			return PERSISTENCE_MODE_UNDEFINED;
		}
		return $this->persistenceMode;
	}

	public function setPersistenceMode(?int $pm):?int{
		if($this->hasPersistenceMode()){
			$this->release($this->persistenceMode);
		}
		return $this->persistenceMode = $this->claim($pm);
	}

	public function hasPersistenceMode():bool{
		return isset($this->persistenceMode);
	}

	public function setEncryptionScheme($scheme){
		$f = __METHOD__;
		if($this->hasEncryptionScheme()){
			$this->release($this->encryptionScheme);
		}
		if(!is_string($scheme)){
			Debug::error("{$f} scheme class name is not a string");
		}elseif(!class_exists($scheme)){
			Debug::error("{$f} invalid encryption scheme \"{$scheme}\"");
		}
		return $this->encryptionScheme = $this->claim($scheme);
	}

	public function hasEncryptionScheme():bool{
		return isset($this->encryptionScheme);
	}

	public function getEncryptionScheme(){
		$f = __METHOD__;
		if(!$this->hasEncryptionScheme()){
			Debug::error("{$f} encryption scheme is undefined");
		}
		return $this->encryptionScheme;
	}
	
	public function volatilize(){
		$this->setPersistenceMode(PERSISTENCE_MODE_VOLATILE);
		return $this;
	}
	
	public static function getCopyableFlags():?array{
		return [
			COLUMN_FILTER_EVENT_SOURCE,
			COLUMN_FILTER_NULLABLE
		];
	}
}
