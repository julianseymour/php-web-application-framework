<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;

class CounterTab extends LabelElement
{

	/*
	 * protected $idPrefix;
	 *
	 * public function hasIdPrefix():bool{
	 * return isset($this->idPrefix);
	 * }
	 *
	 * public function setIdPrefix($prefix){
	 * return $this->idPrefix = $prefix;
	 * }
	 *
	 * public function getIdPrefix(){
	 * $f = __METHOD__; //CounterTab::getShortClass()."(".static::getShortClass().")->getIdPrefix()";
	 * if(!$this->hasIdPrefix()){
	 * Debug::error("{$f} ID prefix is undefined");
	 * }
	 * return $this->idPrefix;
	 * }
	 */
	public function setInnerHTML($innerHTML)
	{
		$f = __METHOD__; //CounterTab::getShortClass()."(".static::getShortClass().")->setInnerHTML()";
		$this->appendChild($innerHTML);
		$counter = new SpanElement();
		$counter->addClassAttribute("notification_counter");
		if(is_object($innerHTML) && ! $innerHTML instanceof StringifiableInterface) {
			$type = $innerHTML->getClass(); // gettype($innerHTML);
			Debug::print("{$f} innerHTML is type \"{$type}\"");
		}
		$lower = NameDatum::normalize($innerHTML);
		$prefix = $this->getIdAttribute(); // $this->getIdPrefix();
		$counter->setIdAttribute("{$prefix}-counter");
		$counter->setInnerHTML(0);
		$counter->setStyleProperty("opacity", "0");
		$this->appendChild($counter);
		return $innerHTML;
	}

	/*
	 * public function dispose():void{
	 * $f = __METHOD__; //ConversationTab::getShortClass()."(".static::getShortClass().")->dispose()";
	 * Debug::printStackTraceNoExit("{$f} entered");
	 * parent::dispose();
	 * }
	 */
}
