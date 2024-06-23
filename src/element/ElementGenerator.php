<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructureClassTrait;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;
use mysqli;

class ElementGenerator extends Basic implements DisposableInterface, EchoJsonInterface, ParentNodeInterface{
	
	use DataStructureClassTrait;
	use ElementBindableTrait;
	use ParentNodeTrait;
	use SelectStatementTrait;
	
	protected $batchSize;
	
	public function __construct(?string $element_class=null, ?string $data_structure_class=null, ?SelectStatement $select=null, ?int $batch_size=500){
		parent::__construct();
		if($element_class !== null){
			$this->setElementClass($element_class);
		}
		if($data_structure_class !== null){
			$this->setDataStructureClass($data_structure_class);
		}
		if($select !== null){
			$this->setSelectStatement($select);
		}
		if($batch_size !== null){
			$this->setBatchSize($batch_size);
		}
	}
	
	public static function declareFlags():?array{
		return array_merge(parent::declareFlags(), [
			"noLimit",
			"noOffset"
		]);
	}
	
	public function echoInnerJson(bool $destroy = false){
		$count = 0;
		foreach($this->generateElements() as $element){
			if($count++ > 0){
				echo ",";
			}
			$element->echoJson($destroy);
			deallocate($element);
		}
	}

	public function echoJson(bool $destroy = false){
		echo "[";
		$this->echoInnerJson($destroy);
		echo "]";
	}

	public function echo(bool $destroy=false){
		foreach($this->generateElements() as $element){
			$element->echo($destroy);
			deallocate($element);
		}
	}
	
	public function skipJson(): bool{
		return false;
	}
	
	public function hasBatchSize():bool{
		return isset($this->batchSize) && is_int($this->batchSize);
	}
	
	public function setBatchSize(?int $size):?int{
		$f = __METHOD__;
		if($size === null){
			unset($this->batchSize);
			return null;
		}elseif(!is_int($size)){
			Debug::error("{$f} batch size is not an integer");
		}elseif($size < 1){
			Debug::error("{$f} illegal batch size {$size}");
		}
		return $this->batchSize = $size;
	}
	
	public function getBatchSize():int{
		$f = __METHOD__;
		if(!$this->hasBatchSize()){
			Debug::error("{$f} batch size is undefined");
		}
		return $this->batchSize;
	}
	
	public function generateElements(?mysqli $mysqli=null, ?string $element_class=null, ?string $data_structure_class=null, ?SelectStatement $select=null, ?int $size=null){
		$f = __METHOD__;
		if($mysqli === null){
			$mysqli = db()->getConnection(PublicReadCredentials::class);
		}
		if($element_class === null){
			$element_class = $this->getElementClass();
		}
		if($data_structure_class === null){
			$data_structure_class = $this->getDataStructureClass();
		}
		if($select === null){
			$select = $this->getSelectStatement();
		}
		if($size === null){
			$size = $this->getBatchSize();
		}
		if(!$select->hasLimit()){
			$this->setFlag("noLimit");
			$select->setLimit($size);
		}
		$count = 0;
		$dc = 0;
		while(true){
			if($count > 0){
				if($select->hasOffset()){
					$select->setOffset($select->getOffset() + $size);
				}else{
					$select->setOffset($size);
					$this->setFlag("noOffset");
				}
			}
			$result = $select->executeGetResult($mysqli);
			if($result->num_rows === 0){
				break;
			}
			$count += $result->num_rows;
			$results = $result->fetch_all(MYSQLI_ASSOC);
			foreach($results as $r){
				$ds = new $data_structure_class();
				$ds->disableDeallocation();
				$status = $ds->processQueryResultArray($mysqli, $r);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultsArray returned error status \"{$err}\" for ".$ds->getDebugString());
					break 2;
				}
				//$status = $ds->loadForeignDataStructures($mysqli, false, 3); //we do not want to load foreign data structures because they would get registered
				$element = new $element_class(ALLOCATION_MODE_LAZY);
				Debug::print("{$f} instantiated ".$dc++." elements");
				if($this->hasParentNode()){
					$element->setParentNode($this->getParentNode());
				}
				$element->bindContext($ds);
				$ds->enableDeallocation();
				yield $element;
			}
		}
		if($this->getFlag("noLimit")){
			$select->setLimit(null);
		}
		if($this->getFlag("noOffset")){
			$select->setOffset(null);
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasParentNode()){
			$this->releaseParentNode($deallocate);
		}
		parent::dispose($deallocate);
		unset($this->batchSize);
		$this->release($this->dataStructureClass, $deallocate);
		$this->release($this->elementClass, $deallocate);
		$this->release($this->selectStatement, $deallocate);
	}
}
