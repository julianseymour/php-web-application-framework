<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

class DummyEmail extends EmailNotificationData
{

	public function getSubjectLine()
	{
		return "";
	}

	public static function getNotificationType()
	{
		return NOTIFICATION_TYPE_TEST;
	}

	public function isOptional()
	{
		return true;
	}

	public function getActionURIPromptMap()
	{
		return [];
	}

	public function getPlaintextBody()
	{
		return "";
	}
}

