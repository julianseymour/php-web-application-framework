<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\StyleElement;
use JulianSeymour\PHPWebApplicationFramework\style\CssRule;

trait MultipleHoneypotsTrait{
	
	protected $honeypotStyleElement;
	
	protected $honeypots;
	
	public function hasHoneypots():bool{
		return isset($this->honeypots);
	}
	
	public function getHoneypots(){
		$f = __METHOD__;
		if(!$this->hasHoneypots()){
			Debug::error("{$f} honeypots are undefined");
		}
		return $this->honeypots;
	}
	
	public function setHoneypots($hp){
		if($this->hasHoneypots()){
			$this->release($this->honeypots);
		}
		return $this->honeypots = $this->claim($hp);
	}
	
	public function hasHoneypotStyleElement():bool{
		return isset($this->honeypotStyleElement);
	}
	
	public function setHoneypotStyleElement($hpse){
		if($this->hasHoneypotStyleElement()){
			$this->release($this->honeypotStyleElement);
		}
		return $this->honeypotStyleElement = $this->claim($hpse);
	}
	
	/**
	 * set the number of decoys per each input
	 *
	 * @return array
	 */
	public static function getHoneypotCountArray(): ?array{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} replace me in a derived class if you want your form to have honeypots");
		}
		return [];
	}
	
	public function getHoneypotStyleElement(): StyleElement{
		if($this->hasHoneypotStyleElement()){
			return $this->honeypotStyleElement;
		}
		$style = new StyleElement();
		$form_id = $this->getIdAttribute();
		$style->setIdAttribute(sha1($form_id . random_bytes(32)));
		$rule = new CssRule();
		$rule->setStyleProperty("display", "none !important");
		$style->appendChild($rule);
		return $this->setHoneypotStyleElement($style);
	}
	
	public function pushHoneypot($pot){
		if(!is_array($this->honeypots)){
			$this->honeypots = [];
		}
		$this->claim($pot);
		array_push($this->honeypots, $pot);
		$pot->setPotNumber(count($this->honeypots));
		return $pot;
	}
}

