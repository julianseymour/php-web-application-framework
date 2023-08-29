<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\paginate\Paginator;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use mysqli;

abstract class PreauthenticationUseCase extends SubsequentUseCase{

	public abstract function getAuthenticatedUserClass();

	public function getExecutePermission(){
		return SUCCESS;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function beforeLoadHook(mysqli $mysqli): int{
		return $this->hasPredecessor() ? $this->getPredecessor()->beforeLoadHook($mysqli) : parent::beforeLoadHook($mysqli);
	}

	public function afterLoadHook(mysqli $mysqli): int{
		return $this->hasPredecessor() ? $this->getPredecessor()->afterLoadHook($mysqli) : parent::afterLoadHook($mysqli);
	}

	public function getDataOperandObject(): ?DataStructure{
		if ($this->hasPredecessor()) {
			return $this->getPredecessor()->getDataOperandObject();
		}
		return parent::getDataOperandObject();
	}

	public function getPageContentGenerator(): UseCase{
		if ($this->hasPredecessor()) {
			return $this->getPredecessor();
		}
		return $this;
	}

	public function getPageContent(): ?array{
		$f = __METHOD__;
		$print = false;
		if ($this->hasPredecessor()) {
			$pcg = $this->getPageContentGenerator();
			if ($print) {
				$pcgc = $pcg->getClass();
				Debug::print("{$f} page content generator class is \"{$pcgc}\"");
			}
			return $pcg->getPageContent();
		}
		Debug::error("{$f} unnimplemented");
	}

	public function setPaginator(?Paginator $paginator): ?Paginator
	{
		if ($this->hasPredecessor()) {
			return $this->getPageContentGenerator()->setPaginator($paginator);
		}
		return parent::setPaginator($paginator);
	}

	public function hasPaginator(): bool
	{
		if ($this->hasPredecessor()) {
			return $this->getPageContentGenerator()->hasPaginator();
		}
		return parent::hasPaginator();
	}

	/*public function getPaginator(): ?Paginator
	{
		if ($this->hasPredecessor()) {
			return $this->getPageContentGenerator()->getPaginator();
		}
		return parent::getPaginator();
	}*/

	protected function getTransitionFromPermission(){
		return SUCCESS;
	}

	public function safeExecute(): int{
		return $this->execute();
	}
	
	public function getDataOperandClass($that = null): string
	{
		if ($this->hasPredecessor()) {
			return $this->getPageContentGenerator()->getDataOperandClass($that);
		}
		return parent::getDataOperandClass($that);
	}

	public function getInsertHereElement(?DataStructure $reloaded = null): Element
	{
		if ($this->hasPredecessor()) {
			return $this->getPageContentGenerator()->getInsertHereElement($reloaded);
		}
		return parent::getInsertHereElement($reloaded);
	}

	public function validateTransition(): int
	{
		$f = __METHOD__; //PreauthenticationUseCase::getShortClass()."(".static::getShortClass().")->validateTransition()";
		$status = parent::validateTransition();
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} parent function returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		// Debug::print("{$f} transition validated");
		$this->transitionValidated = true;
		return SUCCESS;
	}
}
