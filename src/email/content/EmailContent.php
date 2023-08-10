<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\content;

use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\element\ParentNodeTrait;

/**
 * tree-based structure for generating boundary-sepearated email content
 *
 * @author j
 *        
 */
abstract class EmailContent extends Basic implements StringifiableInterface
{

	use ParentNodeTrait;

	public abstract function getContentType();

	public function dispose(): void
	{
		parent::dispose();
		unset($this->parentNode);
	}
}
