<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

class DeleteForeignDataStructureUseCase extends SubsequentUseCase
{

	public function execute(): int
	{
		$f = __METHOD__;
		try{
			$print = false;
			$object = $this->getPredecessor()->getDataOperandObject();
			$post = getInputParameters();
			$index = $post["directive"][DIRECTIVE_DELETE_FOREIGN];
			$datum = null;
			$struct = null;
			if(is_array($index)){
				while($struct == null){
					$keys = array_keys($index);
					$count = count($keys);
					if($count > 1){
						Debug::error("{$f} multiple deletions unsupported");
					}
					$subindex = $keys[0];
					$value = $index[$subindex];
					$datum = $object->getColumn($subindex);
					if($datum instanceof KeyListDatum){
						if(is_array($value)){
							if($print){
								Debug::print("{$f} datum is a key list -- value is an array");
							}
							$key = array_keys($value)[0];
							$object = $object->getForeignDataStructureListMember($subindex, $key);
							$index = $value[$key];
							if(!is_array($index)){
								$datum = $object->getColumn($index);
								$struct = $object->getForeignDataStructure($index);
								break;
							}
						}else{
							if($print){
								Debug::print("{$f} datum is a key list -- value is NOT an array");
							}
							$struct = $object->getForeignDataStructureListMember($subindex, $value);
							$index = $subindex;
							break;
						}
					}elseif($datum instanceof ForeignKeyDatum){
						$object = $object->getForeignDataStructure($subindex);
						if(!is_array($value)){
							if($print){
								Debug::print("{$f} datum is a foreign key -- value is NOT an array");
							}
							$index = $value;
							if($print){
								Debug::print("{$f} about to get foreign data structure at index \"{$index}\"");
							}
							$datum = $object->getColumn($index);
							$struct = $object->getForeignDataStructure($index);
							break;
						}else{
							if($print){
								Debug::print("{$f} datum is a foreign key -- value is an array");
							}
							$subkeys = array_keys($value);
							$index = $value[$subkeys[0]];
							continue;
						}
					}else{
						Debug::error("{$f} neither of the above");
					}
				}
			}else{
				$datum = $object->getColumn($index);
				$struct = $object->getForeignDataStructure($index);
			}
			if(!isset($struct)){
				Debug::error("{$f} foreign data structure is undefined");
			}
			$struct_key = $struct->getIdentifierValue();
			if($print){
				$object_class = $object->getClass();
				$subclass = $struct->getClass();
				if($print){
					Debug::print("{$f} {$object_class}'s subordinate {$subclass} at index \"{$index}\" has key \"{$struct_key}\"");
				}
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if(!isset($mysqli)){
				Debug::error("{$f} mysqli connection error");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$status = $struct->delete($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} struct->deleteYourself returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if($datum instanceof KeyListDatum){
				$object->ejectForeignDataStructureListMember($index, $struct_key);
			}elseif($datum instanceof ForeignKeyDatum){
				$object->ejectForeignDataStructure($index);
			}else{
				Debug::error("{$f} neither of the above during key ejection");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if(!isset($mysqli)){
				Debug::error("{$f} connecting updater returned null");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$status = $object->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} setColumnValue({$index}) returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}