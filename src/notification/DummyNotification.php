<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification;

use JulianSeymour\PHPWebApplicationFramework\data\DeletedObject;

class DummyNotification extends DeletedObject
{

	public $message;

	public function __construct($message)
	{
		$this->message = $message;
	}

	/*
	 * public function to/Array(){
	 * return [ "status"=> SUCCESS, 'info' => $this->message ];
	 * }
	 */
	public function getNotificationType()
	{
		return NOTIFICATION_TYPE_UNDEFINED;
	}

	public function setPushFlag($v)
	{
		return $v;
	}
}