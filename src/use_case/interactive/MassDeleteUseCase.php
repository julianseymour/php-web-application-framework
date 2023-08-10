<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\columns\CounterpartKeyColumnInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\DeleteStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

class MassDeleteUseCase extends SubsequentUseCase
{

	public function execute(): int
	{
		$f = __METHOD__;
		try {
			$print = false;
			$doc = $this->getPredecessor()->getDataOperandClass($this);
			if ($print) {
				Debug::print("{$f} about to process mass deletion of class \"{$doc}\"");
			}
			$form = $this->getPredecessor()->getProcessedFormObject();
			$dummy = $this->getPredecessor()->getDataOperandObject();
			
			$files = null;
			if (request()->hasRepackedIncomingFiles()) {
				$files = request()->getRepackedIncomingFiles();
			}
			$post = getInputParameters();
			$status =  $dummy->processForm($form, $post, $files);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processing form returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$dummy_class = $dummy->getClass();
			if ($dummy_class !== $doc) {
				Debug::error("{$f} dummy class ({$dummy_class}) is not the same as data operand class ({$doc})");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if (! isset($mysqli)) {
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::error("{$f} {$err}");
			} elseif ($dummy instanceof CounterpartKeyColumnInterface) {
				if ($print) {
					Debug::print("{$f} data operand is an instanceof CounterpartKeyColumnInterface");
				}
				$dummy->setRoleAsCounterpart(COUNTERPART_ROLE_INSTIGATOR);
				if ($dummy->getRoleAsCounterpart() !== COUNTERPART_ROLE_INSTIGATOR) {
					Debug::error("{$f} immediately after role assignment, operand is not the instigator");
				}
			} elseif ($print) {
				Debug::print("{$f} data operand does not need a correspondent role");
			}
			$indices = $form->getMassDeletionIndices($dummy);
			if (empty($indices)) {
				Debug::error("{$f} form data indices array is empty");
			} elseif ($print) {
				Debug::print("{$f} about to mass delete by the following form data indices:");
				Debug::printArray($indices);
			}
			$delete = new DeleteStatement();
			$delete->setDatabaseName($dummy->getDatabaseName());
			$delete->setTableName($dummy->getTableName());
			$where = [];
			$params = [];
			$typedef = "";
			foreach ($indices as $column_name) {
				$column = $dummy->getColumn($column_name);
				if(
					$column instanceof ForeignKeyDatum 
					&& $column->getPersistenceMode() === PERSISTENCE_MODE_INTERSECTION
				){
					if($print){
						Debug::print("{$f} column {$column_name} is stored in an intersection table");
					}
					$fdsc = $column->getForeignDataStructureClass();
					$condition = $dummy->whereIntersectionalForeignKey($fdsc, $column_name);
					array_push($where, $condition);
					$value = $dummy->getColumnValue($column_name);
					array_push($params, $value, $column_name);
					$typedef .= $column->getTypeSpecifier()."s";
				}else{
					$condition = new WhereCondition($column_name, OPERATOR_EQUALS);
					array_push($where, $condition);
					$value = $dummy->getColumnValue($column_name);
					if ($print) {
						Debug::print("{$f} mass deleting objects with {$column_name} == {$value}");
					}
					array_push($params, $value);
					$typedef .= $column->getTypeSpecifier();
				}
			}
			$delete->where($where);
			if($print){
				Debug::print("{$f} mass delete query statement is \"{$delete}\"");
			}
			$status = $delete->prepareBindExecuteGetStatus($mysqli, $typedef, ...$params);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} executing mass deletion query returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} mass deletion successful");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
