<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;

/**
 * just a flag called "loaded"
 *
 * @author j
 *        
 */
trait LoadedFlagTrait
{

	use FlagBearingTrait;

	public function getLoadedFlag(): bool
	{
		return $this->getFlag("loaded");
	}

	public function setLoadedFlag(bool $value = true): bool
	{
		return $this->setFlag("loaded", $value);
	}
}
