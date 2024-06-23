<?php

namespace JulianSeymour\PHPWebApplicationFramework\cache;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CacheableTrait{

	use FlagBearingTrait;

	protected $cacheKey;

	protected $timeToLive;

	public function getCacheKey():string{
		$f = __METHOD__;
		if(!$this->hasCacheKey()){
			Debug::error("{$f} cache key is undefined");
		}
		return $this->cacheKey;
	}

	public function hasCacheKey():bool{
		return isset($this->cacheKey);
	}
	
	public function setCacheKey(?string $key):?string{
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){ //valid regex is [a-zA-Z0-9_\\.! ]
			Debug::error("{$f} invalid cache key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} cache key \"{$key}\" is valid");
		}
		if($this->hasCacheKey()){
			$this->release($this->cacheKey);
		}
		return $this->cacheKey = $this->claim($key);
	}

	public function withCacheKey(?string $key):CacheableInterface{
		$this->setCacheKey($key);
		return $this;
	}

	public function setAPCuCacheFlag(bool $value = true): bool{
		return $this->setFlag('APCuCache', $value);
	}

	public function getAPCuCacheFlag(): bool{
		return $this->getFlag('APCuCache');
	}

	public function setFileCacheFlag(bool $value = true): bool{
		return $this->setFlag('fileCache', $value);
	}

	public function getFileCacheFlag(): bool{
		return $this->getFlag('fileCache');
	}

	public function isCacheable():bool{
		return cache()->enabled() && $this->hasCacheKey();
	}

	public function setTimeToLive(?int $duration): ?int{
		if($this->hasTimeToLive()){
			$this->release($this->timeToLive);
		}
		return $this->timeToLive = $this->claim($duration);
	}

	public function hasTimeToLive(): bool{
		return isset($this->timeToLive) && is_int($this->timeToLive) && $this->timeToLive > 0;
	}

	public function getTimeToLive(): int{
		return $this->hasTimeToLive() ? $this->timeToLive : - 1;
	}
}
