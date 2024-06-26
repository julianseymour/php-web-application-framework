<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\substitute;

/**
 * an unsigned integer that measures an interval in minutes
 *
 * @author j
 *        
 */
class MinuteDatum extends UnsignedIntegerDatum{

	protected $multiplier;

	public function getMultiplier(){
		return $this->multiplier;
	}

	public function __construct($name, $bit_count){
		$this->setMultiplier(1);
		parent::__construct($name, $bit_count);
	}

	public function setMultiplier($mult){
		if($this->hasMultiplier()){
			$this->release($this->multiplier);
		}
		return $this->multiplier = $this->claim($mult);
	}

	public function hasMultiplier():bool{
		return isset($this->multiplier);
	}
	
	public function getTimeframeString(){
		$hours = 0;
		$days = 0;
		$multiplier = $this->getMultiplier();
		$minutes = $this->getValue();
		$minutes *= $multiplier;
		if($minutes > 60){
			$hours = floor($minutes / 60);
			$minutes = $minutes % 60;
			if($hours > 24){
				$days = floor($hours / 24);
				$hours = $hours % 24;
			}
		}
		$timeframe = "";
		if($days > 0){
			$timeframe .= substitute(_("%1% days"), $days) . " ";
		}
		if($hours > 0){
			$timeframe .= substitute(_("%1% hours"), $hours) . " ";
		}
		if($minutes > 0){
			$timeframe .= substitute(_("%1% minutes"), $minutes) . " ";
		}
		if(!isset($timeframe) || $timeframe === ""){
			$timeframe = _("Timeframe unavailable");
		}
		return $timeframe;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->multiplier, $deallocate);
	}
}
