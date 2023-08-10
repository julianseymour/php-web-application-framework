<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use JulianSeymour\PHPWebApplicationFramework\email\EmailNotificationData;

abstract class ConfirmationCodeEmail extends EmailNotificationData
{

	protected abstract function getDefaultActionPrompt();

	public function getActionURIPromptMap()
	{
		return [
			$this->getSubjectData()->getConfirmationUri() => $this->getDefaultActionPrompt()
		];
	}
}
