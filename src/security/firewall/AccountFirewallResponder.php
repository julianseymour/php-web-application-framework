<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\cache\CachePageContentCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\DeleteElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\InsertAfterCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class AccountFirewallResponder extends Responder
{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		parent::modifyResponse($response, $use_case);
		$operand = $use_case->getDataOperandObject();
		$type = $operand->getDataType();
		$backup = $use_case->getOriginalOperand();
		// delete the old form
		$iec = $use_case->getConditionalElementClass($type);
		$old_form = new $iec(ALLOCATION_MODE_LAZY, $backup);
		$delete = new DeleteElementCommand($old_form);
		// insert the new one at its appropriate location
		$new_form = new $iec(ALLOCATION_MODE_LAZY, $operand);
		$insert_here = $use_case->getInsertHereElement($operand);
		$insert = new InsertAfterCommand($insert_here, $new_form);
		$delete->pushSubcommand($insert, new CachePageContentCommand());
		$response->pushCommand($delete);
	}
}
