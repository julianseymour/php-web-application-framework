<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

class PseudoclassSelector extends Selector
{

	private $pseudoclass;

	public function setPseudoclass($pseudoclass)
	{
		return $this->pseudoclass = $pseudoclass;
	}

	public function getPseudoclass()
	{
		return $this->pseudoclass;
	}

	public function echo(bool $destroy = false): void
	{
		echo ":{$this->pseudoclass}";
		if($destroy) {
			$this->dispose();
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->pseudoclass);
	}

	public function __construct($pseudoclass = null)
	{
		parent::__construct();
		if(isset($pseudoclass)) {
			$this->setPseudoclass($pseudoclass);
		}
	}
}