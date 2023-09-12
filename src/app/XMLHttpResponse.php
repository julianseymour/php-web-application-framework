<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserKey;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\MultipleCommandsTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

/**
 * This class is used to generate the JSON response to an XMLHttpRequest
 *
 * @author j
 *        
 */
class XMLHttpResponse extends Basic 
implements EchoJsonInterface, StaticPropertyTypeInterface{

	use MultipleCommandsTrait;
	use EchoJsonTrait;
	use EventListeningTrait;
	use StaticPropertyTypeTrait;

	protected $dataStructures;

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"commands" => Command::class
		];
	}

	/**
	 *
	 * @param string $family
	 * @param DataStructure $data
	 */
	public function pushDataStructure(...$data){
		$f = __METHOD__;
		try{
			$print = false;
			if(!is_array($this->dataStructures)) {
				$this->dataStructures = [];
			}
			$count = 0;
			foreach($data as $ds) {
				$member_count = $ds->getFilteredColumnCount(COLUMN_FILTER_ARRAY_MEMBER);
				$a2rc = $ds->getFilteredColumnCount(COLUMN_FILTER_ADD_TO_RESPONSE);
				$key = $ds->getIdentifierValue();
				if($member_count === 0 && $a2rc === 0) {
					$dsc = $ds->getClass();
					$decl = $ds->getDeclarationLine();
					Debug::error("{$f} \"{$dsc}\" with key \"{$key}\" has no array member-flagged columns. It was declared {$decl}");
				}elseif($print) {
					$phylum = $ds->getPhylumName();
					Debug::print("{$f} assigning object from family \"{$phylum}\" with key \"{$key}\"");
				}
				if($member_count > 0) {
					$this->dataStructures[$key] = $ds;
					$count ++;
				}elseif($print) {
					Debug::print("{$f} no columns configured as array members");
				}
				if($a2rc > 0) {
					$columns = $ds->getFilteredColumns(COLUMN_FILTER_FOREIGN);
					if(!empty($columns)) {
						foreach($columns as $column_name => $column) {
							if(!$column->getAddToResponseFlag() || ($column instanceof ForeignKeyDatum && ! $ds->hasForeignDataStructure($column_name)) || ($column instanceof KeyListDatum && ! $ds->hasForeignDataStructureList($column_name))) {
								if($print) {
									Debug::print("{$f} no relations for column \"{$column_name}\", or it lacks an add to response flag");
									if($column_name === "lineItems") {
										if($column->getAddToResponseFlag()) {
											Debug::print("{$f} add to response flag is set");
										}else{
											Debug::print("{$f} add to response flag is not set");
										}
									}
								}
								continue;
							}elseif($print) {
								Debug::print("{$f} foreign data structure at column \"{$column_name}\" is flagged as an addon whenever pushing this data structure to a response");
							}
							if($column instanceof ForeignKeyDatum) {
								$count += $this->pushDataStructure($ds->getForeignDataStructure($column_name));
							}elseif($column instanceof KeyListDatum) {
								if($ds->hasForeignDataStructureList($column_name)) {
									$count += $this->pushDataStructure(...array_values($ds->getForeignDataStructureList($column_name)));
								}
							}else{
								Debug::error("{$f} impossible");
							}
						}
					}elseif($print) {
						Debug::print("{$f} no foreign columns");
					}
				}elseif($print) {
					Debug::print("{$f} no foreign data structures to add to response");
				}
			}
			return $count;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasDataStructure($data){
		$f = __METHOD__;
		if($data instanceof DataStructure) {
			$key = $data->getIdentifierValue();
		}elseif(is_string($data)) {
			$key = $data;
		}else{
			Debug::error("{$f} neither string nor DataStructure");
		}
		return is_array($this->dataStructures) && array_key_exists($key, $this->dataStructures);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"omitCurrentUser",
			"overrideHyperlinkBehavior",
			PROGRESSIVE_HYPERLINK_KEY,
			"refuseCommands"
		]);
	}

	public function setOverrideHyperlinkBehaviorFlag($value){
		return $this->setFlag("overrideHyperlinkBehavior", $value);
	}

	public function getOverrideHyperlinkBehaviorFlag(){
		return $this->getFlag("overrideHyperlinkBehavior");
	}

	public function setOmitCurrentUserFlag($value = true){
		return $this->setFlag("omitCurrentUser", $value);
	}

	public function getOmitCurrentUserFlag(){
		return $this->getFlag("omitCurrentUser");
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		try{
			$print = false;
			$this->setRefuseCommandsFlag(true);
			if(!$this->getOmitCurrentUserFlag()) {
				Json::echoKeyValuePair("currentUserKey", getCurrentUserKey());
			}
			if(is_array($this->properties) && ! empty($this->properties)) {
				foreach($this->getProperties() as $key => $value) {
					if($key === "commands" || $key === "dataStrutures" || $key === "status") {
						continue;
					}
					Json::echoKeyValuePair($key, $value, $destroy);
				}
			}elseif($print) {
				Debug::print("{$f} no properties");
			}
			if($this->hasCommands()) {
				$commands = $this->getCommands();
				if($print) {
					$count = count($commands);
					Debug::print("{$f} {$count} commands");
					foreach($commands as $command) {
						$command->debugPrintSubcommands();
					}
				}
				Json::echoKeyValuePair('commands', $commands, $destroy);
			}elseif($print) {
				Debug::print("{$f} no commands");
			}
			if(is_array($this->dataStructures) && ! empty($this->dataStructures)) {
				Json::echoKeyValuePair('dataStructures', $this->dataStructures, $destroy);
			}elseif($print) {
				Debug::print("{$f} no data structures");
			}
			if(!$this->hasProperty("status")) {
				$this->setProperty("status", SUCCESS);
			}
			Json::echoKeyValuePair("status", $this->getProperty("status"), $destroy, false);
			if($destroy) {
				$this->dispose();
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->dataStructures);
	}

	public function setRefuseCommandsFlag($value){
		return $this->setFlag("refuseCommands", $value);
	}

	public function getRefuseCommandsFlag(){
		return $this->getFlag("refuseCommands");
	}
}

