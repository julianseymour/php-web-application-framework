<?php
namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

abstract class BinarySelector extends Selector
{

	protected $leftHandSide, $rightHandSide;

	public abstract static function echoOperator();

	public function echo(bool $destroy = false): void
	{
		$this->getLeftHandSide()->echo();
		echo " ";
		$this->echoOperator();
		$this->getRightHandSide()->echo();
	}

	public function setLeftHandSide($left)
	{
		return $this->leftHandSide = $left;
	}

	public function setRightHandSide($right)
	{
		return $this->rightHandSide = $right;
	}

	public function getLeftHandSide()
	{
		return $this->leftHandSide;
	}

	public function getRightHandSide()
	{
		return $this->rightHandSide;
	}

	public function __construct($lhs = null, $rhs = null)
	{
		parent::__construct();
		if(isset($lhs)) {
			$this->setLeftHandSide($lhs);
			if(isset($rhs)) {
				$this->setRightHandSide($rhs);
			}
		}
	}

	public function child($chile): Selector
	{
		return new ChildSelector($this, $chile);
	}
}
