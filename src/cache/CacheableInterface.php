<?php

namespace JulianSeymour\PHPWebApplicationFramework\cache;

interface CacheableInterface{

	function getCacheKey():string;

	function hasCacheKey():bool;

	function setCacheKey(?string $key):?string;

	function withCacheKey(?string $key):CacheableInterface;

	function setAPCuCacheFlag(bool $value = true): bool;

	function getAPCuCacheFlag(): bool;

	function setFileCacheFlag(bool $value = true): bool;

	function getFileCacheFlag(): bool;

	function isCacheable():bool;

	function getTimeToLive(): int;

	function hasTimeToLive(): bool;

	function setTimeToLive(?int $duration): ?int;

}
