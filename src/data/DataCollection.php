<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveInterface;
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
use mysqli;

abstract class DataCollection extends Basic implements PermissiveInterface{

	use PermissiveTrait;

	protected $collection;

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->collection, $deallocate);
		$this->release($this->permissionGateway, $deallocate);
		if($this->hasPermissions()){
			$this->releasePermissions($deallocate);
		}
		$this->release($this->singlePermissionGateways, $deallocate);
	}

	public function collect($struct){
		if(!isset($this->collection) || !is_array($this->collection)){
			$this->collection = [];
		}
		return $this->collection[$struct->getIdentifierValue()] = $this->claim($struct);
	}

	public function beforeInsertHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_INSERT)){
			$this->dispatchEvent(new BeforeInsertEvent());
		}
		return SUCCESS;
	}

	public function afterInsertHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_INSERT)){
			$this->dispatchEvent(new AfterInsertEvent());
		}
		return SUCCESS;
	}

	public final function insert(mysqli $mysqli):int{
		$f = __METHOD__;
		$status = $this->permit(user(), DIRECTIVE_INSERT);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} permit returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$status = $this->beforeInsertHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before insert hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		foreach($this->getCollection() as $item){
			$status = $item->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				$id = $item->getIdentifierValue();
				Debug::warning("{$f} inserting item with identifier \"{$id}\" returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$status = $this->afterInsertHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after insert hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}

	public function beforeUpdateHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_UPDATE)){
			$this->dispatchEvent(new BeforeUpdateEvent());
		}
		return SUCCESS;
	}

	public function afterUpdateHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_UPDATE)){
			$this->dispatchEvent(new AfterUpdateEvent());
		}
		return SUCCESS;
	}

	public final function update(mysqli $mysqli):int{
		$f = __METHOD__;
		$status = $this->permit(user(), DIRECTIVE_UPDATE);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} permit returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$status = $this->beforeUpdateHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before update hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		foreach($this->getCollection() as $item){
			$status = $item->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				$id = $item->getIdentifierValue();
				Debug::warning("{$f} updating item with identifier \"{$id}\" returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$status = $this->afterUpdateHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after update hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}

	public function beforeDeleteHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_DELETE)){
			$this->dispatchEvent(new BeforeDeleteEvent());
		}
		return SUCCESS;
	}

	public function afterDeleteHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_DELETE)){
			$this->dispatchEvent(new AfterDeleteEvent());
		}
		return SUCCESS;
	}

	public final function delete(mysqli $mysqli):int{
		$f = __METHOD__;
		$status = $this->permit(user(), DIRECTIVE_DELETE);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} permit returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$status = $this->beforeDeleteHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before delete hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		foreach($this->getCollection() as $item){
			$status = $item->delete($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				$id = $item->getIdentifierValue();
				Debug::warning("{$f} deleting item with identifier \"{$id}\" returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$status = $this->afterDeleteHook($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after delete hook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}
}
