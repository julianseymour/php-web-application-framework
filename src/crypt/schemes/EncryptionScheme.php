<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;

abstract class EncryptionScheme extends DatumBundle
{

	protected $datum;

	public abstract static function encrypt($value, $key, $nonce = null);

	public abstract static function decrypt($cipher, $key, $nonce = null);

	public abstract function extractEncryptionKey($datum);

	public abstract function extractDecryptionKey($datum);

	public abstract function extractNonce($datum);

	public function __construct($datum = null)
	{
		parent::__construct();
		if (isset($datum)) {
			$this->pushColumn($datum);
		}
	}

	public function hasColumn()
	{
		return isset($this->datum) && $this->datum instanceof Datum;
	}

	public function getColumn()
	{
		$f = __METHOD__; //EncryptionScheme::getShortClass()."(".static::getShortClass().")->getColumn()";
		if (! $this->hasColumn()) {
			Debug::error("{$f} datum is undefined");
		}
		return $this->datum;
	}

	public function pushColumn($datum)
	{
		$f = __METHOD__; //EncryptionScheme::getShortClass()."(".static::getShortClass().")->pushColumn()";
		if (! isset($datum) || ! is_object($datum) || ! $datum instanceof Datum) {
			Debug::error("{$f} invalid datum");
		}
		return $this->datum = $datum;
	}
}
