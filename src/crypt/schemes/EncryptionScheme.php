<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;

abstract class EncryptionScheme extends DatumBundle{

	protected $datum;

	public abstract static function encrypt(string $value, string $key, ?string $nonce = null):string;

	public abstract static function decrypt(string $cipher, string $key, ?string $nonce = null):?string;

	public abstract function extractEncryptionKey(Datum $datum):?string;

	public abstract function extractDecryptionKey(Datum $datum):?string;

	public abstract function extractNonce(Datum $datum):?string;

	public function __construct(?Datum $datum = null){
		parent::__construct();
		if (isset($datum)) {
			$this->setColumn($datum);
		}
	}

	public function hasColumn():bool{
		return isset($this->datum) && $this->datum instanceof Datum;
	}

	public function getColumn():Datum{
		$f = __METHOD__;
		if (! $this->hasColumn()) {
			Debug::error("{$f} datum is undefined");
		}
		return $this->datum;
	}

	public function setColumn(Datum $datum):Datum{
		$f = __METHOD__;
		if (! isset($datum) || ! is_object($datum) || ! $datum instanceof Datum) {
			Debug::error("{$f} invalid datum");
		}
		return $this->datum = $datum;
	}
	
	public function pushColumn(Datum $datum):int{
		$this->setColumn($datum);
		return 1;
	}
}
