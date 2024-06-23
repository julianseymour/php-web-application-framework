<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\pwa;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\element\inline\AnchorElement;

class ProgressiveHyperlinkElement extends AnchorElement{

	protected $progressiveHyperlinkFunction;

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->progressiveHyperlinkFunction, $deallocate);
	}

	public function setProgressiveHyperlinkFunction(?string $func):?string{
		if($this->hasProgressiveHyperlinkFunction()){
			$this->release($this->progressiveHyperlinkFunction);
		}
		
		return $this->progressiveHyperlinkFunction = $this->claim($func);
	}

	protected function hasProgressiveHyperlinkFunction():bool{
		return isset($this->progressiveHyperlinkFunction);
	}

	protected function getProgressiveHyperlinkFunction():string{
		if($this->hasProgressiveHyperlinkFunction()){
			return $this->progressiveHyperlinkFunction;
		}
		return "loadHyperlink";
	}

	public function withProgressiveHyperlinkFunction(?string $func):ProgressiveHyperlinkElement{
		$this->setProgressiveHyperlinkFunction($func);
		return $this;
	}

	protected function beforeRenderHook():int{
		$ret = parent::beforeRenderHook();
		$phf = $this->getProgressiveHyperlinkFunction();
		$onclick = "{$phf}(event, this)";
		$this->setOnClickAttribute($onclick);
		return $ret;
	}

	public function setCacheAttribute($value){
		return $this->setAttribute("cache", $value);
	}

	public function getCacheAtteribute(){
		return $this->getAttribute("cache");
	}

	public function hasCacheAttribute():bool{
		return $this->hasAttribute("cache");
	}

	public function cache($value = "1"): ProgressiveHyperlinkElement{
		$this->setCacheAttribute($value);
		return $this;
	}

	public static function link(string $href, string $innerHTML, ?string $func = null, bool $cache = false): ProgressiveHyperlinkElement{
		$p = new ProgressiveHyperlinkElement();
		$p->setHrefAttribute($href);
		$p->setInnerHTML($innerHTML);
		if($func !== null){
			$p->setProgressiveHyperlinkFunction($func);
		}
		if($cache){
			$p->cache();
		}
		return $p;
	}

	public static function links(?array $arr, ?string $func = null, bool $cache = false): ?array{
		if(empty($arr)){
			return [];
		}
		$ret = [];
		foreach($arr as $href => $innerHTML){
			array_push($ret, static::link($href, $innerHTML, $func, $cache));
		}
		return $ret;
	}
}
