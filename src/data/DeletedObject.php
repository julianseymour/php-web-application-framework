<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\core\Basic;

class DeletedObject extends Basic{
	
	public $type;
	public $key;
	
	public function expandTree($mysqli):int{
		return SUCCESS;
	}
	
	public static function isDeleted():bool{
		return true;
	}
	
	public static function getMessagePreview():string{
		return _("New conversation");
	}
	
	public function hasIdentifierValue(){
		return false;
	}
	
	public static function getMessageBox(){
		return MESSAGE_BOX_OUTBOX;
	}
	
	public function delete(){
		return STATUS_DELETED;
	}
	
	public function getName():string{
		return _("Deleted");
	}
	
	public function setDataType($type){
		return $this->type = $type;
	}
	
	public function getDataType():string{
		return $this->type;
	}
	
	public function __construct(){
		parent::__construct();
		$this->type = DATATYPE_UNKNOWN;
	}
	
	public function setIdentifierValue($key){
		return $this->key = $key;
	}
	
	public function getIdentifierValue(){
		return $this->key;
	}
}