<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

abstract class ReinsertElementCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	use ReferenceElementIdTrait;

	// protected $insertWhere;
	public abstract static function getInsertWhere();

	public static function getCommandId(): string
	{
		return "reinsert";
	}

	public function __construct($inserted_element, $insert_here)
	{
		$f = __METHOD__; //ReinsertElementCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($inserted_element);
		if(is_string($insert_here)) {
			$this->setReferenceElementId($insert_here);
		}elseif(is_object($insert_here) && $insert_here instanceof Element) {
			$this->setReferenceElementId($insert_here->getIdAttribute());
		}else{
			Debug::error("{$f} invalid insertion target");
		}
	}

	/*
	 * public function setInsertWhere($where){
	 * $f = __METHOD__; //ReinsertElementCommand::getShortClass()."(".static::getShortClass().")->setInsertWhere()";
	 * switch($where){
	 * case "after":
	 * case "before":
	 * case "appendChild":
	 * case "prependChild":
	 * case "unshiftChild":
	 * break;
	 * default:
	 * Debug::error("{$f} invalid insert preposition \"{$where}\"");
	 * return null;
	 * }
	 * return $this->insertionWhere = $where;
	 * }
	 */

	/*
	 * public function hasInsertWhere(){
	 * return isset($this->insertWhere);
	 * }
	 */

	/*
	 * public function getInsertWhere(){
	 * $f = __METHOD__; //ReinsertElementCommand::getShortClass()."(".static::getShortClass().")->getInsertWhere()";
	 * if(!$this->hasInsertWhere()){
	 * Debug::error("{$f} insert where is undefined");
	 * }
	 * return $this->insertWhere;
	 * }
	 */
	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //ReinsertElementCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		Json::echoKeyValuePair('insert_here', $this->getReferenceElementId(), $destroy);
		Json::echoKeyValuePair('where', $this->getInsertWhere(), $destroy);
		parent::echoInnerJson($destroy);
	}

	/*
	 * public function dispose():void{
	 * parent::dispose();
	 * unset($this->insertWhere);
	 * }
	 */
	public function resolve()
	{
		$f = __METHOD__; //ReinsertElementCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		ErrorMessage::unimplemented($f);
	}
}
