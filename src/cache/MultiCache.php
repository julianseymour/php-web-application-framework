<?php

namespace JulianSeymour\PHPWebApplicationFramework\cache;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class MultiCache extends Basic implements \Psr\SimpleCache\CacheInterface{

	protected $APCuCachePool;

	protected $filesystemCachePool;

	public function enabled(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		return defined('CACHE_ENABLED') && CACHE_ENABLED === true; // && $this->getFlag("enabled");
	}

	protected function hasFilesystemCachePool(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		return isset($this->filesystemCachePool) && $this->filesystemCachePool instanceof \Cache\Adapter\Filesystem\FilesystemCachePool;
	}

	protected function getFilesystemCachePool(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		if($this->hasFilesystemCachePool()) {
			return $this->filesystemCachePool;
		}
		return $this->filesystemCachePool = new \Cache\Adapter\Filesystem\FilesystemCachePool(new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local("/var/www")));
	}

	protected function hasAPCuCachePool(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		return isset($this->APCuCachePool) && $this->APCuCachePool instanceof \Cache\Adapter\Apcu\ApcuCachePool;
	}

	public function getAPCuCachePool(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		if($this->hasAPCuCachePool()) {
			if($print){
				Debug::print("{$f} already allocated, returning");
			}
			return $this->APCuCachePool;
		}elseif($print){
			Debug::print("{$f} returning new ApcuCachePool");
		}
		return $this->APCuCachePool = new \Cache\Adapter\Apcu\ApcuCachePool();
	}

	public function has($key): bool{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered with key \"{$key}\"");
			}
			if($key instanceof CacheableInterface) {
				if(!$key->isCacheable()) {
					return false;
				}
				$key = $key->getCacheKey();
			}
			if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
				Debug::error("{$f} invalid key \"{$key}\"");
			}
			return $this->getAPCuCachePool()->has($key) || $this->getFilesystemCachePool()->has($key);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function get($key, $default = null){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered with key \"{$key}\"");
		}
		if($key instanceof CacheableInterface) {
			if(!$key->isCacheable()) {
				Debug::error("{$f} uncacheable object");
			}
			$key = $key->getCacheKey();
		}
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}
		if($this->getAPCuCachePool()->has($key)) {
			return $this->APCuCachePool->get($key);
		}
		if($this->getFilesystemCachePool()->has($key)) {
			return $this->filesystemCachePool->get($key);
		}
		return null;
	}

	public function set($key, $value, $ttl = null): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		if($key instanceof CacheableInterface) {
			if(!$key->isCacheable()) {
				Debug::warning("{$f} uncacheable object");
				throw new \Psr\SimpleCache\InvalidArgumentException("Uncachable object");
				return false;
			}
			$key = $key->getCacheKey();
		}
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}
		global $__START;
		if(isset($ttl) && is_int($ttl) && $ttl > 0 && $ttl < $__START) {
			$ttl += $__START;
		}
		$this->getAPCuCachePool()->set($key, $value, $ttl);
		$this->getFilesystemCachePool()->set($key, $value, $ttl);
		return true;
	}

	public function delete($key): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		if($key instanceof CacheableInterface) {
			if(!$key->isCacheable()) {
				Debug::warning("{$f} uncacheable object");
			}elseif(!$key->hasCacheKey()) {
				throw new \Psr\SimpleCache\InvalidArgumentException("Object lacks a cache key");
				return false;
			}
			$key = $key->getCacheKey();
		}
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}
		if($this->getAPCuCachePool()->has($key)) {
			$this->APCuCachePool->delete($key);
		}
		if($this->getFilesystemCachePool()->has($key)) {
			$this->filesystemCachePool->delete($key);
		}
		return true;
	}

	public function expire($key, int $ttl): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		if($key instanceof CacheableInterface) {
			if(!$key->isCacheable()) {
				Debug::warning("{$f} uncacheable object");
			}elseif(!$key->hasCacheKey()) {
				throw new \Psr\SimpleCache\InvalidArgumentException("Object lacks a cache key");
				return false;
			}
			$key = $key->getCacheKey();
		}
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}
		global $__START;
		if(isset($ttl) && is_int($ttl) && $ttl > 0 && $ttl < $__START) {
			$ttl += $__START;
		}
		if($this->getAPCuCachePool()->has($key)) {
			$this->APCuCachePool->set($key, $this->APCuCachePool->get($key), $ttl);
		}
		if($this->getFilesystemCachePool()->has($key)) {
			$this->filesystemCachePool->set($key, $this->filesystemCachePool->get($key), $ttl);
		}
		return true;
	}

	public function expireAPCu($key, int $ttl){
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered");
		}
		global $__START;
		if(isset($ttl) && is_int($ttl) && $ttl > 0 && $ttl < $__START) {
			$ttl += $__START;
		}
		$pool = $this->getAPCuCachePool();
		return $pool->set($key, $pool->get($key), $ttl);
	}

	public function expireFile($key, int $ttl){
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered");
		}
		global $__START;
		if(isset($ttl) && is_int($ttl) && $ttl > 0 && $ttl < $__START) {
			$ttl += $__START;
		}
		$pool = $this->getFilesystemCachePool();
		return $pool->set($key, $pool->get($key), $ttl);
	}

	public function clearAPCu():bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		$this->getAPCuCachePool()->clear();
		return true;
	}

	public function clearFile(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		$this->getFilesystemCachePool()->clear();
		return true;
	}

	public function clear(){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		$this->clearAPCu();
		$this->clearFile();
		return true;
	}

	public function getMultiple($keys, $default = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
		if($this->hasAPCuCachePool()) {
			return $this->APCuCachePool->getMultiple($keys, $default);
		}
		if($this->hasFilesystemCachePool()) {
			return $this->filesystemCachePool->getMultiple($keys, $default);
		}
		return [];
	}

	public function setMultiple($values, $ttl = null): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		$this->getAPCuCachePool()->setMultiple($values, $ttl);
		$this->getFilesystemCachePool()->setMultiple($values, $ttl);
		return true;
	}

	public function deleteMultiple($keys): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} entered");
		}
		return $this->getAPCuCachePool()->deleteMultiple($keys) && $this->getFilesystemCachePool()->deleteMultiple($keys);
	}

	public function hasFile($key): bool{
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered");
		}
		return $this->getFilesystemCachePool()->has($key);
	}

	public function getFile($key, $default = null){
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered");
		}
		return $this->getFilesystemCachePool()->get($key, $default);
	}

	public function setFile($key, $value, $ttl = null): bool{
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered");
		}
		global $__START;
		if(isset($ttl) && is_int($ttl) && $ttl > 0 && $ttl < $__START) {
			$ttl += $__START;
		}
		return $this->getFilesystemCachePool()->set($key, $value, $ttl);
	}

	public function hasAPCu($key): bool{
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered with key \"{$key}\"");
		}
		$pool = $this->getAPCuCachePool();
		if($print){
			Debug::print("{$f} returning");
		}
		return $pool->has($key);
	}

	public function getAPCu($key, $default = null){
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered with key \"{$key}\"");
		}
		$pool = $this->getAPCuCachePool();
		if($print){
			Debug::print("{$f} returning");
		}
		return $pool->get($key, $default);
	}

	public function setAPCu($key, $value, $ttl = null): bool{
		$f = __METHOD__;
		$print = false;
		if(preg_match('|[\{\}\(\)/\\\@\:]|', $key)){
			Debug::error("{$f} invalid key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} entered with key \"{$key}\"");
		}
		global $__START;
		if(isset($ttl) && is_int($ttl) && $ttl > 0 && $ttl < $__START) {
			$ttl += $__START;
		}
		return $this->getAPCuCachePool()->set($key, $value, $ttl);
	}
}
