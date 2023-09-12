<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use mysqli;

abstract class DataCollection extends Basic
{

	use EventListeningTrait;
	use PermissiveTrait;

	protected $collection;

	public function dispose(): void
	{
		parent::dispose();
		unset($this->collection);
	}

	public function collect($struct)
	{
		if(! isset($this->collection) || ! is_array($this->collection)) {
			$this->collection = [];
		}
		return $this->collection[$struct->getIdentifierValue()] = $struct;
	}

	protected function beforeInsertHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new BeforeInsertEvent());
		return SUCCESS;
	}

	protected function afterInsertHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new AfterInsertEvent());
		return SUCCESS;
	}

	public final function insert($mysqli)
	{
		$f = __METHOD__; //DataCollection::getShortClass()."(".static::getShortClass().")->insert()";
		$status = $this->permit(user(), DIRECTIVE_INSERT);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} permit returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$status = $this->beforeInsertHook($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before insert hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		foreach($this->getCollection() as $item) {
			$status = $item->insert($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				$id = $item->getIdentifierValue();
				Debug::warning("{$f} inserting item with identifier \"{$id}\" returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$status = $this->afterInsertHook($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after insert hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}

	protected function beforeUpdateHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new BeforeUpdateEvent());
		return SUCCESS;
	}

	protected function afterUpdateHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new AfterUpdateEvent());
		return SUCCESS;
	}

	public final function update($mysqli)
	{
		$f = __METHOD__; //DataCollection::getShortClass()."(".static::getShortClass().")->update()";
		$status = $this->permit(user(), DIRECTIVE_UPDATE);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} permit returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$status = $this->beforeUpdateHook($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before update hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		foreach($this->getCollection() as $item) {
			$status = $item->update($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				$id = $item->getIdentifierValue();
				Debug::warning("{$f} updating item with identifier \"{$id}\" returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$status = $this->afterUpdateHook($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after update hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}

	protected function beforeDeleteHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new BeforeDeleteEvent());
		return SUCCESS;
	}

	protected function afterDeleteHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new AfterDeleteEvent());
		return SUCCESS;
	}

	public final function delete($mysqli)
	{
		$f = __METHOD__; //DataCollection::getShortClass()."(".static::getShortClass().")->delete()";
		$status = $this->permit(user(), DIRECTIVE_DELETE);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} permit returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$status = $this->beforeDeleteHook($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before delete hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		foreach($this->getCollection() as $item) {
			$status = $item->delete($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				$id = $item->getIdentifierValue();
				Debug::warning("{$f} deleting item with identifier \"{$id}\" returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$status = $this->afterDeleteHook($mysqli);
		if($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after delete hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}
}
