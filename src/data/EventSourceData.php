<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumCryptoSignatureDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameTrait;

/**
 * data structure for automating finite state datum update event sourcing.
 * can be used for other stuff as well
 *
 * @author j
 *        
 */
class EventSourceData extends UserOwned implements StaticDatabaseNameInterface{

	use StaticDatabaseNameTrait;
	
	protected $stateColumn;

	public function __construct(?Datum $column = null, ?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		if($column !== null) {
			$this->stateColumn = $column;
		}else{
			Debug::error("{$f} state column is undefined");
		}
		parent::__construct($mode);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$print = false;
		parent::declareColumns($columns, $ds);
		$sc = $ds->getStateColumn();
		$params = $sc->getConstructorParams();
		if(count($params) > 1) {
			$params = array_slice($params, 1, count($params) - 1);
		}else{
			$params = [];
		}
		$tc = $sc->getClass();
		$token = new TextDatum("token");
		$token->setNullable(true);
		// current state
		$current_state = new $tc("currentState", ...$params);
		// previous state
		$previous_state = new $tc("previousState", ...$params);
		$previous_state->setNullable(true);
		// object key constrained
		$target_key = new ForeignMetadataBundle("target", $ds);
		$target_key->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$target = $sc->getDataStructure();
		$target_class = $target->getClass();
		if($target->hasColumnValue("subtype") || $target instanceof StaticSubtypeInterface) {
			$subtype = $target->getSubtype();
			if($print) {
				Debug::print("{$f} target {$target_class} subtype is {$subtype}");
			}
		}else{
			if($print) {
				Debug::print("{$f} target {$target_class} does not have a subtype");
			}
			$subtype = null;
		}
		$target_key->setForeignDataStructureClass(mods()->getDataStructureClass($target->getDataType(), $subtype));
		// comment
		$comment = new TextDatum("comment");
		$comment->setNullable(true);
		$signature = new SodiumCryptoSignatureDatum("signature");
		$signature->setSignColumnNames(array_merge(array_keys($columns), [
			"currentState",
			"previousState",
			"token",
			"targetKey",
			"targeyDataType",
			"targetSubtype"
		]));
		array_push($columns, $current_state, $previous_state, $token, $target_key, $comment, $signature);
	}

	public static function getPermissionStatic(string $name, $data){
		if($name === DIRECTIVE_INSERT) {
			return SUCCESS;
		} // XXX TODO delete this
		elseif($name === DIRECTIVE_CREATE_TABLE) {
			return SUCCESS;
		}

		return FAILURE;
	}

	public function hasStateColumn():bool{
		return isset($this->stateColumn);
	}

	public function getStateColumn(){
		$f = __METHOD__;
		if(!$this->hasStateColumn()) {
			Debug::error("{$f} state column is undefined");
		}
		return $this->stateColumn;
	}

	public function setPreviousState($value){
		return $this->setColumnValue("previousState", $value);
	}

	public function setCurrentState($value){
		return $this->setColumnValue("currentState", $value);
	}

	public function setToken($value){
		return $this->setColumnValue("token", $value);
	}

	public static function getDatabaseNameStatic(): string{
		return "events";
	}

	public function getTableName(): string{
		$sc = $this->getStateColumn();
		return $sc->getDataStructure()->getTableName() . "_" . $sc->getName();
	}

	public static function getPrettyClassName():string{
		return _("Event source");
	}

	public static function getDataType(): string{
		return DATATYPE_EVENT_SOURCE;
	}

	public static function getPrettyClassNames():string{
		return _("Event sources");
	}

	public static function getPhylumName(): string{
		return "events";
	}

	public function hasTargetKey(): bool{
		return $this->hasColumnValue("targetKey");
	}

	public function getTargetKey(): ?string{
		return $this->getColumnValue("targetKey");
	}

	public function setTargetKey(?string $value): ?string{
		return $this->setColumnValue("targetKey", $value);
	}

	public function ejectTargetKey(): ?string{
		return $this->ejectColumnValue("targetKey");
	}

	public function setTargetData(?DataStructure $struct): ?DataStructure{
		return $this->setForeignDataStructure("targetKey", $struct);
	}

	public function hasTargetData(): bool{
		return $this->hasForeignDataStructure("targetKey");
	}

	public function getTargetData(): ?DataStructure
	{
		return $this->getForeignDataStructure("targetKey");
	}

	public function ejectTargetData(): ?DataStructure
	{
		return $this->ejectForeignDataStructure("targetKey");
	}
}
