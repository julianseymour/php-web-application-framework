<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\XMLHttpResponse;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetTextContentCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\ui\infobox\InfoBoxCommand;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SearchResponder extends Responder{

	public function modifyResponse(XMLHttpResponse $response, UseCase $use_case){
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				Debug::print("{$f} entered");
			}
			$status = $use_case->getObjectStatus();
			if($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} status \"{$err}\"");
			}
			$response->setProperties([
				"status" => $status
			]);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error status \"{$err}\"");
				$response->pushCommand(new InfoBoxCommand(ErrorMessage::getVisualError($status)));
				return;
			}elseif(! hasInputParameter("searchQuery")) {
				if($print) {
					Debug::print("{$f} search was not conducted");
				}
				return;
			}elseif(!$use_case->hasSearchResults()) {
				if($print) {
					Debug::print("{$f} zero search results");
				}
				$response->pushCommand(new InfoBoxCommand(ErrorMessage::getVisualError(ERROR_0_SEARCH_RESULTS)));
				return;
			}elseif($print) {
				Debug::print("{$f} about to return search results");
			}
			parent::modifyResponse($response, $use_case);
			$objects = $use_case->getSearchResults();
			if($use_case->hasPredecessor()) {
				if($print) {
					Debug::print("{$f} predecessor is defined");
				}
				$predecessor = $use_case->getPredecessor();
			}else{
				if($print) {
					Debug::print("{$f} predecessor is undefined");
				}
				$predecessor = $use_case;
			}
			$elements = [];
			$template = false;
			foreach($objects as $obj) {
				$element_class = $predecessor->getSearchResultElementClass($obj);
				if($element_class::isTemplateworthy()) {
					$template = true;
					if($print) {
						Debug::print("{$f} element class \"{$element_class}\" is templateworthy");
					}
					$obj->setElementClass($element_class);
					if(!$obj->hasElementClass()) {
						Debug::error("{$f} element class is undefined immediately after it was set");
					}elseif($print) {
						Debug::print("{$f} set element class to \"{$element_class}\"");
					}
				}else{
					if($template) {
						Debug::error("{$f} cannot mix template and non-templateworthy element classes in results");
					}elseif($print) {
						Debug::print("{$f} element class \"{$element_class}\" is not templateworthy");
					}
					$element = new $element_class(ALLOCATION_MODE_LAZY, $obj);
					array_push($elements, $element);
				}
				$obj->configureArrayMembership("search");
			}
			if($template) {
				if($print) {
					Debug::print("{$f} search results are templateworthy, pushing results as data");
				}
				$response->pushDataStructure(...$objects);
			}else{
				if($print) {
					Debug::print("{$f} search results are not templateworthy");
				}
				$insert_here = $predecessor->getInsertHereElement(null);
				$delete_command = new SetTextContentCommand($insert_here, "");
				$insert_command = new AppendChildCommand($insert_here, ...$elements);
				$delete_command->pushSubcommand($insert_command);
				$response->pushCommand($delete_command);
			}
			if($use_case->hasPredecessor()) {
				if($print) {
					Debug::print("{$f} about to tell the predecessor to modify response");
				}
				$status = $predecessor->getObjectStatus();
				$responder = $predecessor->getResponder($status);
				if($responder instanceof Responder) {
					$responder->modifyResponse($response);
				}
			}elseif($print) {
				Debug::print("{$f} no defined predecessor");
			}
			return;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}