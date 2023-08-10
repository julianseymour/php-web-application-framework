<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class NotificationsResponder extends Responder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		try {
			$print = false;
			parent::modifyResponse($response, $use_case);
			$tree_name = NotificationData::getPhylumName();
			if (! user()->hasForeignDataStructureList($tree_name)) {
				if ($print) {
					Debug::print("{$f} there are no notifications");
				}
				return;
			} elseif ($print) {
				Debug::print("{$f} about to push notifications");
			}
			$notifications = array_reverse(user()->getForeignDataStructureList($tree_name));
			foreach ($notifications as $note) {
				$note_key = $note->getIdentifierValue();
				if (! $note->hasSubjectData()) {
					if($print){
						Debug::print("{$f} notification with key \"{$note_key}\" is missing target object");
					}
					continue;
				}
				$target = $note->getSubjectData();
				$status = $target->getObjectStatus();
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} notification target has object status \"{$err}\"");
					continue;
				}
				$config = $note->getArrayMembershipConfiguration(CONST_DEFAULT);
				if ($print) {
					$note_class = $note->getClass();
					Debug::print("{$f} got the following array configuration for a {$note_class}:");
					Debug::printArray($config);
				}
				$note->configureArrayMembership($config);
				$response->pushDataStructure($note);
			}
			if ($print) {
				Debug::print("{$f} returning normally");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}