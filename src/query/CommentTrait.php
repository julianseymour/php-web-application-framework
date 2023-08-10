<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait CommentTrait
{

	protected $commentString;

	public function setComment($comment)
	{
		$f = __METHOD__; //"CommentTrait(".static::getShortClass().")->setComment()";
		try {
			if ($comment == null) {
				unset($this->commentString);
				return null;
			} elseif (! is_string($comment)) {
				$comment = "{$comment}";
			}
			return $this->commentString = $comment;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasComment()
	{
		return isset($this->commentString);
	}

	public function getComment()
	{
		$f = __METHOD__; //"CommentTrait(".static::getShortClass().")->getComment()";
		if (! $this->hasComment()) {
			Debug::error("{$f} comment is undefined");
		}
		return $this->commentString;
	}

	public function comment($comment)
	{
		$this->setComment($comment);
		return $this;
	}
}