<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\recent;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\online\UpdateOnlineStatusIndicatorCommand;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class RecentNotificationsResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		if (user()->hasForeignDataStructureList("notifications")) {
			foreach (user()->getForeignDataStructureList("notifications") as $note) {
				$note->configureArrayMembership(CONST_DEFAULT);
				$response->pushDataStructure($note);
			}
		}
		$delivery = user()->getNotificationDeliveryTimestamp();
		if (user()->hasForeignDataStructureList("online")) {
			foreach (user()->getForeignDataStructureList("online") as $note) {
				if ($note->getNotificationType() !== NOTIFICATION_TYPE_MESSAGE) {
					continue;
				} elseif (! $note->hasCorrespondentObject()) {
					continue;
				}
				$contact = $note->getCorrespondentObject();
				if ($contact->getLastSeenTimestamp() > $delivery || ($contact->hasColumn("logoutTimestamp") && ($contact->getLogoutTimestamp() > $delivery))) {
					$contact->setCorrespondentObject(user());
					$command = new UpdateOnlineStatusIndicatorCommand($contact);
					$response->pushCommand($command);
				}
			}
		}
		parent::modifyResponse($response, $use_case);
	}
}
