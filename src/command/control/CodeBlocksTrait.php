<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;

trait CodeBlocksTrait
{

	use ArrayPropertyTrait;

	// protected $codeBlocks;
	public function setCodeBlocks($blocks)
	{
		return $this->setArrayProperty("codeBlocks", $blocks);
		/*
		 * $arr = [];
		 * foreach($blocks as $b){
		 * array_push($arr, $b);
		 * }
		 * return $this->codeBlocks = $arr;
		 */
	}

	public function hasCodeBlocks()
	{
		return $this->hasArrayProperty("codeBlocks"); // is_array($this->codeBlocks) && !empty($this->codeBlocks);
	}

	public function getCodeBlocks()
	{
		return $this->getProperty("codeBlocks");
	}

	public function pushCodeBlocks(...$blocks)
	{
		return $this->pushArrayProperty("codeBlocks", ...$blocks);
		/*
		 * if(!is_array($this->codeBlocks)){
		 * $this->codeBlocks = [];
		 * }
		 * array_push($this->codeBlocks, $b);
		 * return $b;
		 */
	}
}