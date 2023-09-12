<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\index\MultipleIndexNamesTrait;
use Exception;

class IndexHint extends Basic implements StaticPropertyTypeInterface, SQLInterface
{

	use MultipleIndexNamesTrait;
	use StaticPropertyTypeTrait;

	protected $indexHintType;

	protected $forWhat;

	public function __construct(?string $type = null)
	{
		parent::__construct();
		// $this->requirePropertyType("indexNames", "s");
		if($type !== null) {
			$this->setIndexHintType($type);
		}
	}

	public function setIndexHintType($type)
	{
		$f = __METHOD__; //IndexHint::getShortClass()."(".static::getShortClass().")->setIndexHintType()";
		if($type == null) {
			unset($this->indexHintType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} index hint type must be a string");
		}elseif(empty($type)) {
			Debug::error("{$f} index hint type cannot be empty string");
		}
		$type = strtolower($type);
		switch ($type) {
			case INDEX_HINT_TYPE_USE:
			case INDEX_HINT_TYPE_IGNORE:
			case INDEX_HINT_TYPE_FORCE:
				break;
			default:
				Debug::error("{$f} invalid index hint type \"{$type}\"");
		}
		return $this->indexHintType = $type;
	}

	public function hasIndexHintType()
	{
		return isset($this->indexHintType) && is_string($this->indexHintType) && ! empty($this->indexHintType);
	}

	public function getIndexHintType()
	{
		$f = __METHOD__; //IndexHint::getShortClass()."(".static::getShortClass().")->getIndexHIntType()";
		if(!$this->hasIndexHintType()) {
			Debug::error("{$f} index hint type is undefined");
		}
		return $this->indexHintType;
	}

	public function setFor($what)
	{
		$f = __METHOD__; //IndexHint::getShortClass()."(".static::getShortClass().")->setFor()";
		if($what == null) {
			unset($this->forWhat);
			return null;
		}elseif(!is_string($what)) {
			Debug::error("{$f} index hint for variable must be a string");
		}elseif(empty($what)) {
			Debug::error("{$f} empty string");
		}
		$what = strtolower($what);
		switch ($what) {
			case INDEX_HINT_FOR_GROUP_BY:
			case INDEX_HINT_FOR_JOIN:
			case INDEX_HINT_FOR_ORDER_BY:
				break;
			default:
				Debug::error("{$f} invalid index hint type \"{$what}\"");
		}
		return $this->forWhat = $what;
	}

	public function hasFor()
	{
		return isset($this->forWhat) && is_string($this->forWhat) && ! empty($this->forWhat);
	}

	public function getFor()
	{
		$f = __METHOD__; //IndexHint::getShortClass()."(".static::getShortClass().")->getFor()";
		if(!$this->hasFor()) {
			Debug::error("{$f} for is undefined");
		}
		return $this->forWhat;
	}

	public function for($what)
	{
		$this->setFor($what);
		return $this;
	}

	public static function useIndex()
	{
		return new IndexHint(INDEX_HINT_TYPE_USE);
	}

	public static function ignoreIndex()
	{
		return new IndexHint(INDEX_HINT_TYPE_IGNORE);
	}

	public static function forceIndex()
	{
		return new IndexHint(INDEX_HINT_TYPE_FORCE);
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //IndexHint::getShortClass()."(".static::getShortClass().")->__toString()";
		try{
			// USE {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
			// {IGNORE|FORCE} {INDEX|KEY} [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
			$type = $this->getIndexHintType();
			$string = "{$type} index ";
			if($this->hasFor()) {
				$string .= "for " . $this->getFor();
			}
			$string .= "(";
			if($this->hasIndexNames()) {
				$string .= implode(',', $this->getIndexNames());
			}elseif($type !== INDEX_HINT_TYPE_USE) {
				Debug::error("{$f} index names are requiired for ignore and force index hint types");
				return null;
			}
			$string .= ")";
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->forWhat);
		unset($this->indexHintType);
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"indexNames" => 's'
		];
	}
}
