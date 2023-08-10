<?php
namespace JulianSeymour\PHPWebApplicationFramework\cache;

interface CacheableInterface
{

	function getCacheKey();

	function hasCacheKey();

	function setCacheKey($key);

	function withCacheKey($key);

	function setAPCuCacheFlag(bool $value = true): bool;

	function getAPCuCacheFlag(): bool;

	function setFileCacheFlag(bool $value = true): bool;

	function getFileCacheFlag(): bool;

	function isCacheable();

	function getTimeToLive(): int;

	function hasTimeToLive(): bool;

	function setTimeToLive(?int $duration): ?int;
	// function toArray($config=null);
}
