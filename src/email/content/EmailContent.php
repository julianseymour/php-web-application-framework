<?php

namespace JulianSeymour\PHPWebApplicationFramework\email\content;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\element\ParentNodeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\ParentNodeTrait;

/**
 * tree-based structure for generating boundary-sepearated email content
 *
 * @author j
 *        
 */
abstract class EmailContent extends Basic implements ParentNodeInterface, StringifiableInterface{

	use ParentNodeTrait;

	public abstract function getContentType();

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		unset($this->parentNode); //, $deallocate, $this->getDebugString());
	}
}
