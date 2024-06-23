<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait CommentTrait{

	protected $comment;

	public function setComment($comment){
		$f = __METHOD__;
		try{
			if(!is_string($comment)){
				$comment = "{$comment}";
			}elseif($this->hasComment()){
				$this->release($this->comment);
			}
			return $this->comment = $this->claim($comment);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasComment():bool{
		return isset($this->comment);
	}

	public function getComment(){
		$f = __METHOD__;
		if(!$this->hasComment()){
			Debug::error("{$f} comment is undefined");
		}
		return $this->comment;
	}

	public function withComment($comment){
		$this->setComment($comment);
		return $this;
	}
}