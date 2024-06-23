<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

/**
 * An interface for objects used as a resource by multiple related objects that want to deallocate it once it is no longer useful. 
 * Hit points are depleted in deallocate(). 
 * @author j
 *
 */
interface HitPointsInterface{
	
	public function getHitPoints():int;
	
	public function setHitPoints(int $hp):int;
	
	public function hasHitPoints():bool;
	
	public function hpUp(int $hp=1):int;
	
	public function damageHP(int $dmg=1):int;
}