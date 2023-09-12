<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\DeleteElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\CheckInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\notification\ui\NotificationOptionsElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\messenger\notification\MessageNotificationData;
use JulianSeymour\messenger\notification\MessageNotificationOptionsElement;
use Exception;

class PinNotificationResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case)
	{
		$f = __METHOD__;
		$print = false;
		try{
			parent::modifyResponse($response, $use_case);
			$note_data = $use_case->getDataOperandObject();
			$note_type = $note_data->getNotificationType();
			$key = $note_data->getIdAttributeSuffixCommand();
			// 1. update notification element's options
			$note_options = new NotificationOptionsElement(ALLOCATION_MODE_LAZY, $note_data);
			if(!$note_options->hasIdAttribute()) {
				Debug::error("{$f} NotificationOptionsElement lacks an ID attribute");
			}elseif($print) {
				$id = $note_options->getIdAttribute();
				while ($id instanceof ValueReturningCommandInterface) {
					$id = $id->evaluate();
				}
				Debug::print("{$f} NotificationOptionsElement ID attribute is \"{$id}\"");
				if(!$note_options->hasIdAttribute()) {
					Debug::error("{$f} getting the ID attribute destroys it");
				}
				$id = $note_options->getIdAttribute();
				while ($id instanceof ValueReturningCommandInterface) {
					$id = $id->evaluate();
				}
				Debug::print("{$f} ID attribute is still \"{$id}\"");
			}
			$update1 = $note_options->update();
			// 2. delete strawman
			$delete1 = new DeleteElementCommand(new ConcatenateCommand("notification_strawman-", $key));
			$update1->pushSubcommand($delete1);
			$check1 = new CheckInputCommand("notification_options-none");
			$check1->pushSubcommand($update1);
			// 3. if it's not a message notification, return early
			if($note_type !== NOTIFICATION_TYPE_MESSAGE) {
				$response->pushCommand($check1);
				return;
			}
			// 4. otherwise, close conversation label options
			$check2 = new CheckInputCommand("messenger_options-none");
			// 5. update conversation label wrapper's options
			$msg_note_data = new MessageNotificationData();
			$msg_note_data->copy($note_data);
			$conv_options = new MessageNotificationOptionsElement(ALLOCATION_MODE_LAZY, $msg_note_data);
			$update2 = $conv_options->update();
			// 6. delete conversation label wrapper's strawman
			$delete2 = new DeleteElementCommand(new ConcatenateCommand("messenger_strawman-", $key));
			$update2->pushSubcommand($delete2);
			$check2->pushSubcommand($update2);
			// 7. if the form was submitted thru the conversation label wrapper, it is dominant and the other command is optional
			if(getInputParameter('widget') === 'messenger') {
				$check1->setOptional(true);
				$update1->setOptional(true);
				$check2->pushSubcommand($check1);
				$response->pushCommand($check2);
				return;
			}
			// 8. otherwise, conversation label update command is optional and subordinate
			$check2->setOptional(true);
			$update2->setOptional(true);
			$check1->pushSubcommand($check2);
			$response->pushCommand($check1);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
