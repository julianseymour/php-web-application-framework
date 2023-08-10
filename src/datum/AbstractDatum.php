<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * base class for Datum and DatumBundle
 *
 * @author j
 *        
 */
abstract class AbstractDatum extends Basic implements ArrayKeyProviderInterface
{

	use ArrayPropertyTrait;

	/**
	 * specifies default value or expression to generate default value
	 *
	 * @var mixed
	 */
	protected $defaultValue;

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
	 *
	 * @var string
	 */
	// protected $ConverseRelationshipKeyName;

	/**
	 * Where the value is stored for persistence.
	 * Not to be confused with Datum->databaseStorageType.
	 *
	 * @var int
	 */
	protected $persistenceMode;

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			COLUMN_FILTER_EVENT_SOURCE,
			COLUMN_FILTER_NULLABLE
		]);
	}

	public function setEventSourceFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_EVENT_SOURCE, $value);
	}

	public function getEventSourceFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_EVENT_SOURCE);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->defaultValue);
		unset($this->embeddedName);
		unset($this->encryptionScheme);
		unset($this->persistenceMode);
	}

	public function embed(string $group_name)
	{
		$f = __METHOD__; //AbstractDatum::getShortClass()."(".static::getShortClass().")->embed()";
		if (! is_string($group_name)) {
			Debug::error("{$f} group name must be a string");
		} elseif (empty($group_name)) {
			Debug::error("{$f} group name cannot be empty");
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_EMBEDDED);
		$this->embeddedName = $group_name;
		return $this;
	}

	public function hasEmbeddedName()
	{
		return isset($this->embeddedName) && is_string($this->embeddedName) && ! empty($this->embeddedName);
	}

	public function getEmbeddedName()
	{
		$f = __METHOD__; //AbstractDatum::getShortClass()."(".static::getShortClass().")->getEmbeddedName()";
		if (! $this->hasEmbeddedName()) {
			$cn = $this->getColumnName();
			Debug::error("{$f} embed group name is undefined for column \"{$cn}\"");
		}
		return $this->embeddedName;
	}

	/**
	 * where is the data stored -- nowhere, mysql database (default), session, cookies, local storage, indexed db, remote
	 *
	 * @return int
	 */
	public function getPersistenceMode()
	{
		if (! $this->hasPersistenceMode()) {
			return CONST_UNDEFINED;
		}
		return $this->persistenceMode;
	}

	public function setPersistenceMode($pm)
	{
		return $this->persistenceMode = $pm;
	}

	public function hasPersistenceMode()
	{
		return isset($this->persistenceMode);
	}

	public function setEncryptionScheme($scheme)
	{
		$f = __METHOD__; //AbstractDatum::getShortClass()."(".static::getShortClass().")->setEncryptionScheme()";
		if (! is_string($scheme)) {
			Debug::error("{$f} scheme class name is not a string");
		} elseif (! class_exists($scheme)) {
			Debug::error("{$f} invalid encryption scheme \"{$scheme}\"");
		}
		return $this->encryptionScheme = $scheme;
	}

	public function hasEncryptionScheme()
	{
		return isset($this->encryptionScheme) && is_string($this->encryptionScheme) && class_exists($this->encryptionScheme);
	}

	public function getEncryptionScheme()
	{
		$f = __METHOD__; //AbstractDatum::getShortClass()."(".static::getShortClass().")->getEncryptionScheme()";
		if (! $this->hasEncryptionScheme()) {
			Debug::error("{$f} encryption scheme is undefined");
		}
		return $this->encryptionScheme;
	}

	public function setDefaultValue($v)
	{
		if ($v === null) {
			$this->setNullable(true);
		}
		return $this->defaultValue = $v;
	}

	public function getDefaultValue()
	{
		if ($this->hasDefaultValue()) {
			return $this->defaultValue;
		}
		return null;
	}

	public function hasDefaultValue()
	{
		return $this->defaultValue !== null;
	}

	public function withDefaultValue($value)
	{
		$this->setDefaultValue($value);
		return $this;
	}

	public function getDefaultValueString()
	{
		return $this->getDefaultValue();
	}

	public function setNullable($value = true)
	{
		return $this->setFlag(COLUMN_FILTER_NULLABLE, $value);
	}

	public function isNullable()
	{
		return $this->getFlag(COLUMN_FILTER_NULLABLE);
	}

	public function volatilize()
	{
		$this->setPersistenceMode(PERSISTENCE_MODE_VOLATILE);
		return $this;
	}
}
