<?php

namespace JulianSeymour\PHPWebApplicationFramework\email\content;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class MultipartEmailContent extends EmailContent{

	use ArrayPropertyTrait;

	public abstract static function getMultipartType();

	public abstract static function getPrefix();

	protected $boundary;

	public function __construct(...$children){
		$this->setBoundary(uniqid($this->getPrefix()));
		if(isset($children)){
			$this->setChildNodes($children);
		}
	}

	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasBoundary()){
			$this->release($this->boundary, $deallocate);
		}
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
	
	public function setChildNodes(?array $children): ?array{
		$children = $this->setArrayProperty("childNodes", $children);
		if(!empty($children)){
			$this->adoptChildNodes($children);
		}
		return $children;
	}

	public function adoptChildNodes(array $children): array{
		$f = __METHOD__;
		$print = false;
		if(is_array($children) && !empty($children)){
			if($print){
				$count = count($children);
				Debug::print("{$f} adopting {$count} child nodes");
			}
			foreach($children as $child){
				if($child instanceof EmailContent){
					$child->setParentNode($this);
				}
			}
		}else{
			Debug::error("{$f} this function accepts only non-empty arrays");
		}
		return $children;
	}

	public function hasChildNodes(): bool{
		return $this->hasArrayProperty("childNodes");
	}

	public function getChildNodes(): ?array{
		return $this->getProperty("childNodes");
	}

	public function hasBoundary(): bool{
		return isset($this->boundary);
	}

	public function getBoundary(): string{
		$f = __METHOD__;
		if(!$this->hasBoundary()){
			Debug::error("{$f} boundary is undefined");
		}
		return $this->boundary;
	}

	public function setBoundary(?string $boundary): ?string{
		if($this->hasBoundary()){
			$this->release($this->boundary);
		}
		return $this->boundary = $this->claim($boundary);
	}

	public function __toString(): string{
		$f = __METHOD__;
		$ret = "";
		$eol = "\r\n";
		$boundary = $this->getBoundary();
		if($this->hasParentNode()){
			$content_type = $this->getContentType();
			$ret .= "Content-Type:{$content_type}{$eol}";
		}
		// child nodes
		if($this->hasChildNodes()){
			foreach($this->getChildNodes() as $child){
				// boundary 1+
				$ret .= "{$eol}{$eol}--{$boundary}{$eol}";
				$ret .= $child;
			}
		}else{
			Debug::error("{$f} child nodes are undefined");
		}
		$ret .= "{$eol}{$eol}--{$boundary}{$eol}";
		return $ret;
	}

	public function getContentType(){
		$multipart = static::getMultipartType();
		$boundary = $this->getBoundary();
		return "multipart/{$multipart};boundary={$boundary}";
	}
}
