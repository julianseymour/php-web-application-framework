<?php

namespace JulianSeymour\PHPWebApplicationFramework\cache;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;

class CacheResponder extends Responder{

	public function __construct(bool $cache = true){
		parent::__construct();
		$this->setCacheFlag($cache);
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"cache"
		]);
	}

	public function setCacheFlag(bool $value = true): bool{
		return $this->setFlag("cache", $value);
	}

	public function getCacheFlag(): bool{
		return $this->getFlag("cache");
	}
}
