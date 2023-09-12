<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use Exception;
use mysqli;

class CommonTableExpression extends QueryStatement implements StaticPropertyTypeInterface
{

	use MultipleColumnNamesTrait;
	use NamedTrait;
	use StaticPropertyTypeTrait;

	protected $subquery;

	public function __construct($name, $subquery)
	{
		parent::__construct();
		// $this->requirePropertyType("columnNames", "s");
		$this->setName($name);
		$this->setSubquery($subquery);
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"columnNames" => 's'
		];
	}

	public function setSubquery($subquery)
	{
		$f = __METHOD__; //CommonTableExpression::getShortClass()."(".static::getShortClass().")->setSubquery()";
		if($subquery == null) {
			unset($this->subquery);
			return null;
		}
		return $this->subquery = $subquery;
	}

	public function hasSubquery()
	{
		return isset($this->subquery);
	}

	public function getSubquery()
	{
		$f = __METHOD__; //CommonTableExpression::getShortClass()."(".static::getShortClass().")->getSubquery()";
		if(!$this->hasSubquery()) {
			Debug::error("{$f} subquery is undefined");
		}
		return $this->subquery;
	}

	public function getQueryStatementString()
	{
		$f = __METHOD__; //CommonTableExpression::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		try{
			// cte_name [(col_name [, col_name] ...)] AS (subquery)
			$string = $this->getName();
			if($this->hasColumnNames()) {
				$string .= " (" . implode_back_quotes(',', $this->getColumnNames()) . ")";
			}
			$string .= " as (" . $this->getSubquery() . ")";
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * returns true if the server supports common table expressions, false otherwise
	 *
	 * @param mysqli $mysqli
	 * @return bool
	 */
	public static function isSupported($mysqli = null): bool
	{
		if($mysqli == null) {
			$mysqli = db()->getConnection(PublicReadCredentials::class);
		}
		Debug::error($mysqli->server_info);
		return false;
	}
}
