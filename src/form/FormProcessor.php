<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\FileData;
use JulianSeymour\PHPWebApplicationFramework\input\CheckedInput;
use JulianSeymour\PHPWebApplicationFramework\input\FileInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;

class FormProcessor extends Basic{
	
	public function processFormInput(DataStructure $that, string $column_name, InputlikeInterface $input, int &$unchanged, ?array $arr, ?array $files): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				$input_class = $input->getClass();
				Debug::print("{$f} about to bind datum \"{$column_name}\" to a {$input_class}");
			}
			$column = $that->getColumn($column_name);
			$input->bindContext($column);
			// have the input process input parameters to look for its value
			if(is_array($arr)){
				if($print){
					Debug::print("{$f} processing array for input \"{$column_name}\"");
				}
				$status = $input->processArray($arr);
				if($status !== SUCCESS){
					if($print){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processArray for {$input_class} input {$column_name} returned error status \"{$err}\"");
					}
					return $column->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} input variable that is supposed to be an array is not");
			}
			// CheckedInput has an implicit value of false if its name is not found
			if($input instanceof CheckedInput && !$input->hasCheckedAttribute()){
				if($print){
					Debug::print("{$f} input is a checkbox or something like that and unchecked");
				}
				if($column instanceof BooleanDatum){
					$column->setValue(false);
				}elseif($column->hasValue()){
					if($print){
						Debug::print("{$f} datum has a value");
					}
					$column->ejectValue();
				}elseif($print){
					Debug::print("{$f} datum does not have a value");
				}
				return SUCCESS;
			}elseif(
				!$input instanceof CheckedInput &&
				!$input instanceof FileInput &&
				!$input->hasValueAttribute() &&
				!$column->getSensitiveFlag()
				){// input isn't a checkbox/file and has no apparent value
					if($print){
						Debug::print("{$f} input at column \"{$column_name}\" does not have a value attribute");
						Debug::printArray($arr);
					}
					if($column->getProcessValuelessInputFlag()){
						if($print){
							Debug::print("{$f} column \"{$column_name}\" is flagged to process valueless input anyway");
						}
					}else{
						if($print){
							Debug::print("{$f} column \"{$column_name}\" does is NOT flagged to process valueless inputs");
						}
						if($column->hasValue()){
							if($print){
								Debug::print("{$f} column \"{$column_name}\" already has a value; ejecting it now");
							}
							$column->ejectValue();
							return SUCCESS;
						}else{
							$unchanged ++;
							if($print){
								Debug::print("{$f} column \"{$column_name}\" does not have a value; incremented unchanged column count to {$unchanged}");
							}
							return STATUS_UNCHANGED;
						}
					}
			}elseif($print){
				Debug::print("{$f} input \"{$column_name}\" has a value attribute");
			}
			if($print){
				$column_class = $column->getClass();
				Debug::print("{$f} about to call {$column_class}->processInput() for column \"{$column_name}\"");
			}
			// have the datum ask the input to transfer a value
			$status = $column->processInput($input);
			if($input instanceof FileInput && $that instanceof FileData){
				if($print){
					Debug::print("{$f} input at column \"{$column_name}\" is a file input");
				}
				$subfiles = null;
				if(is_array($files) && array_key_exists($column_name, $files)){
					if($print){
						Debug::print("{$f} name \"{$column_name}\" exists in the files array");
					}
					$subfiles = $files[$column_name];
				}elseif($print){
					Debug::print("{$f} name \"{$column_name}\" does NOT exist in the files array");
					Debug::print($files);
				}
				if(!empty($subfiles)){
					if($print){
						Debug::print("{$f} about to process an array of repacked incoming files");
					}
					$status = $that->processRepackedIncomingFiles($subfiles);
				}else{
					$sub_arr = is_array($arr) && array_key_exists($column_name, $arr) ? $arr[$column_name] : null;
					if(!empty($sub_arr) && array_key_exists("resized", $sub_arr)){
						if($print){
							Debug::print("{$f} time to process resized file");
						}
						$status = $that->processResizedFiles($sub_arr);
					}elseif($print){
						Debug::print("{$f} not going to bother looking for resized files");
					}
				}
			}elseif($print){
				Debug::print("{$f} input at column \"{$column_name}\" is not a file input");
			}
			if($status === STATUS_UNCHANGED){
				if($print){
					Debug::print("{$f} column \"{$column_name}\" did not change");
				}
				$unchanged ++;
			}elseif($print){
				Debug::print("{$f} column \"{$column_name}\" changed");
			}
			if($print){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} after processing for input for column \"{$column_name}\" returning status code \"{$err}\" with unchanged column count {$unchanged}");
			}
			return $status;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * process the input parameters of a submitted AjaxForm
	 *
	 * @param AjaxForm $form
	 *        	: instance of the form that was submitted
	 * @param array $arr
	 *        	: input parameters to process
	 * @param array $files
	 *        	: files submitted with the form
	 * @return int
	 */
	public function processForm(DataStructure $that, AjaxForm $form, ?array $arr, ?array $files = null): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} about to process the following array:");
				Debug::printArray($arr);
			}
			// $that->setFlag("processedForm", true);
			$input_classes = $form->getFormDataIndices($that);
			if($print){
				$fc = $form->getClass();
				Debug::print("{$f} about to process the following input classes for form \"{$fc}\":");
				Debug::printArray($input_classes);
			}
			$unchanged = 0;
			foreach($input_classes as $column_name => $input_class){
				if($print){
					Debug::print("{$f} column name \"{$column_name}\"; current unchanged column count is {$unchanged}");
				}
				$column = $that->getColumn($column_name);
				$input = new $input_class(ALLOCATION_MODE_FORM);
				///$input->setColumnName($column_name);
				// if the input is a subordinate form, match the subordinate form the the foreign data structure at the same index
				if($input instanceof AjaxForm){
					$print = false;
					if($print){
						Debug::print("{$f} column name \"{$column_name}\" is a subordinate form");
					}
					if(is_array($files) && array_key_exists($column_name, $files)){
						if($print){
							Debug::print("{$f} yes, there are incoming files to process");
						}
						$subfiles = $files[$column_name];
					}else{
						if($print){
							Debug::print("{$f} no, there are no incoming files to process");
						}
						$subfiles = null;
					}
					if(is_array($arr) && array_key_exists($column_name, $arr)){
						$sub_arr = $arr[$column_name];
						if($print){
							Debug::print("{$f} about to print sub array \"{$column_name}\"");
							Debug::printarray($sub_arr);
						}
					}else{
						$sub_arr = null;
					}
					if($print && empty($sub_arr)){
						Debug::warning("{$f} key \"{$column_name}\" does not exist");
						Debug::printArray($arr);
					}
					if($column instanceof KeyListDatum){
						if(!is_array($sub_arr)){
							Debug::error("{$f} subordinate array at column name \"{$column_name}\" is not an array");
						}
						$child_count = count($sub_arr);
						if($print){
							Debug::print("{$f} processing {$child_count} objects for a KeyListDatum");
						}
						if($child_count > 0){
							$unchanged_children = 0;
							foreach(array_keys($sub_arr) as $child_key){
								if(is_array($sub_arr) && array_key_exists($child_key, $sub_arr)){
									$child_arr = $sub_arr[$child_key];
								}else{
									$child_arr = null;
								}
								if(is_array($subfiles) && array_key_exists($child_key, $subfiles)){
									$child_files = $subfiles[$child_key];
								}else{
									$child_files = null;
								}
								if(!empty($child_arr) || !empty($child_files)){
									if($print){
										Debug::print("{$f} about to process subordinate form at column name \"{$column_name}\" with iterator \"{$child_key}\"");
									}
									$status = $this->processNestedForm($that, $form, $child_arr, $child_files, $column_name);
									switch($status){
										case STATUS_UNCHANGED:
											if($print){
												Debug::print("{$f} one item has not changed");
											}
											$unchanged_children ++;
											continue 2;
										case SUCCESS:
											break;
										default:
											$err = ErrorMessage::getResultMessage($status);
											Debug::error("{$f} processing subordinate form at column name \"{$column_name}\" returned error status \"{$err}\"");
									}
								}elseif($print){
									Debug::print("{$f} subordinate form at column \"{$column_name}\" will not be processed");
								}
							}
							if($unchanged_children === count($sub_arr)){
								$unchanged ++;
								if($print){
									Debug::print("{$f} unchanged child object count for column \"{$column_name}\" is equal to the number of children; incremented unchanged column count to {$unchanged}");
								}
							}else{
								if($print){
									Debug::print("{$f} at least one foreign data structure list member changed; unchanged column count remains the same at {$unchanged}");
								}
								$status = SUCCESS;
							}
							$unchanged_children = 0;
						}
					}elseif($column instanceof ForeignKeyDatum){
						if(!empty($sub_arr) || !empty($subfiles)){
							if($print){
								Debug::print("{$f} about to process subordinate form at column \"{$column_name}\"");
							}
							$status = $this->processNestedForm($that, $form, $sub_arr, $subfiles, $column_name);
							// this was added after disabling the increment on line 5265
							if($status === STATUS_UNCHANGED){
								if($print){
									Debug::print("{$f} processing subordinate form for column \"{$column_name}\" resulted in no change");
								}
								$unchanged++;
							}elseif($print){
								Debug::print("{$f} processing subordinate form resulted in a change to foreign data structure \"{$column_name}\"");
							}
						}elseif($print){
							Debug::print("{$f} subordinate form at column \"{$column_name}\" will not be processed");
						}
					}else{
						Debug::error("{$f} neither of the above");
					}
				}else{ // input is not a subordinate form
					$form->attachNegotiator($input);
					$status = $this->processFormInput($that, $column_name, $input, $unchanged, $arr, $files);
				}
				// deal with results of handling input processing for this column
				if(!isset($status)){
					Debug::error("{$f} status is undefined");
					return $that->setObjectStatus(FAILURE);
				}elseif($status === SUCCESS){
					if($print){
						Debug::print("{$f} assigned value to column name \"{$column_name}\"; unchanged column count is {$unchanged}");
					}
				}elseif($status === STATUS_UNCHANGED){
					if($print){
						Debug::print("{$f} value at column \"{$column_name}\" did not change");
					}
					continue;
				}else{
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processInput for datum at column \"{$column_name}\" returned error status \"{$err}\"");
					return $that->setObjectStatus($status);
				}
			}
			$status = $that->getObjectStatus();
			if($status === STATUS_UNCHANGED){
				if($print){
					Debug::print("{$f} this object was marked unchanged at some point");
				}
				return $status;
			}elseif($unchanged === count($input_classes)){
				if($print){
					Debug::print("{$f} nothing changed ({$unchanged} unchanged columns)");
				}
				return STATUS_UNCHANGED;
			}elseif($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * processes the input parameters of a subordinate form (i.e.
	 * one that is subindexed in a superior form) with the foreign data structure at index $column_name
	 * XXX TODO this function is a behemoth that needs to be broken up into several smaller ones
	 *
	 * @param AjaxForm $superior_form
	 *        	: Nextmost superior form containing the subindexed inputs of the subordinate
	 * @param array $arr
	 *        	: parameters to be processed by subordinate form
	 * @param array $files
	 *        	: files to be processed by subordinate form
	 * @param array $column_name
	 *        	: column name of this object's foreign key datum whose foreign data structure will process the subordinate form, and index in the superior form of the subordinate form for that foreign key datum
	 * @return int
	 */
	protected function processNestedForm(DataStructure $that, AjaxForm $superior_form, ?array $arr, ?array $files, string $column_name){
		$f = __METHOD__;
		try{
			$print = false;
			if(!isset($superior_form)){
				Debug::error("{$f} this function now requires a form class to retrieve subordinate form data indices");
			}
			$column = $that->getColumn($column_name);
			$subclass = $column->getForeignDataStructureClass($that);
			$mode = $column->getUpdateBehavior();
			switch($mode){
				case FOREIGN_UPDATE_BEHAVIOR_DELETE:
					if($print){
						Debug::print("{$f} delete foreign update behavior");
					}
					if(is_a($subclass, FileData::class, true)){
						if($print){
							Debug::print("{$f} {$subclass} is a FileData");
						}
						$delete = is_array($files); // && array_key_exists($column_name, $files);
						if($print){
							if($delete){
								Debug::print("{$f} going to delete the old FileData");
							}elseif(!is_array($files)){
								Debug::print("{$f} files array is not an array");
							}elseif(!array_key_exists($column_name, $files)){
								Debug::print("{$f} key \"{$column_name}\" is not present is files array");
								Debug::printArray($files);
							}
						}
					}else{
						if($print){
							Debug::print("${f} {$subclass} is not a FileData");
						}
						$delete = true;
					}
					break;
				case FOREIGN_UPDATE_BEHAVIOR_NORMAL:
					if($print){
						Debug::print("{$f} default foreign update behavior");
					}
					$delete = false;
					break;
				default:
					Debug::error("{$f} invalid update behavior \"{$mode}\"");
			}
			if($column instanceof ForeignKeyDatum){
				if($print){
					Debug::print("{$f} datum is a foreign key datum");
				}
				$multiple = false;
			}elseif($column instanceof KeyListDatum){
				if($print){
					Debug::print("{$f} datum is a key list datum");
				}
				$multiple = true;
			}else{
				Debug::error("{$f} neither of the above");
			}
			// get the data structure (same as old one)
			$existing = false;
			$idn = $subclass::getIdentifierNameStatic();
			if((!$multiple && $that->hasForeignDataStructure($column_name)) || ($multiple && array_key_exists($idn, $arr) && $that->hasForeignDataStructureListMember($column_name, $arr[$idn]))){
				if($print){
					Debug::print("{$f} there is already an existing data structure at index \"{$column_name}\"");
				}
				if($multiple){
					$old_struct = $that->getForeignDataStructureListMember($column_name, $arr[$idn]);
				}else{
					$old_struct = $that->getForeignDataStructure($column_name);
				}
				if($old_struct->isDeleted() ){
					if($print){
						Debug::print("{$f} old data structure was already deleted");
					}
					if($multiple){
						$that->ejectForeignDataStructureListMember($column_name, $arr[$idn]);
					}else{
						$that->ejectForeignDataStructure($column_name);
					}
					$old_struct = null;
					$nested_structure = new $subclass();
					if($print){
						Debug::print("{$f} subordinate {$subclass} was deleted");
					}
				}else{
					$existing = true;
					if($print){
						Debug::print("{$f} a foreign data structure for column \"{$column_name}\" already exists");
					}
					if($delete){
						if($print){
							Debug::print("{$f} existing data structure is to be deleted");
						}
						$nested_structure = new $subclass();
					}else{
						if($print){
							Debug::print("{$f} existing data struture at index \"{$column_name}\" is not to be deleted");
						}
						$nested_structure = $old_struct;
					}
				}
			}else{
				if($print){
					Debug::print("{$f} creating new subordinate data structure of class \"{$subclass}\"");
				}
				$nested_structure = new $subclass();
			}
			// process subordinate form
			$nested_form = $superior_form->getNestedForm($column_name, $nested_structure);
			if($print){
				$sfc = $nested_form->getClass();
				Debug::print("{$f} subordinate form class for column \"{$column_name}\" is \"{$sfc}\"");
			}
			$processor = new FormProcessor();
			$status = $processor->processForm($nested_structure, $nested_form, $arr, $files);
			$this->disableDeallocation();
			$that->disableDeallocation();
			$nested_structure->disableDeallocation();
			$nested_form->disableDeallocation();
			deallocate($processor);
			$this->enableDeallocation();
			$that->enableDeallocation();
			$nested_structure->enableDeallocation();
			$nested_form->enableDeallocation();
			switch($status){
				case STATUS_UNCHANGED:
					if($print){
						Debug::print("{$f} processing form for subordinate structure does nothing useful");
					}
					return STATUS_UNCHANGED;
				case SUCCESS:
					if($print){
						Debug::print("{$f} successfully processed form");
					}
					if($nested_structure->getDeleteFlag()){
						if($print){
							Debug::print("{$f} subordinate structure is flagged for deletion");
						}
					}
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} processing subordinate form returned error status \"{$err}\"");
					return $status;
			}
			// flag existing structure for update
			if($existing){
				if($print){
					Debug::print("{$f} there was an existing data structure at column \"{$column_name}\"");
				}
				if($delete){
					if($print){
						Debug::print("{$f} foreign data structure \"{$column_name}\" wants you to delete the thing it's replacing");
					}
					if(DataStructure::equals($old_struct, $nested_structure)){
						Debug::print("{$f} old data structure has not changed a bit");
						return SUCCESS;
					}
					if($multiple){
						$that->setOldDataStructureListMember($column_name, $old_struct);
					}else{
						$that->setOldDataStructure($column_name, $old_struct);
					}
					$old_struct->setDeleteFlag(true);
					$that->setDeleteOldDataStructuresFlag(true);
				}else{
					if($print){
						Debug::print("{$f} foreign data structure \"{$column_name}\" wants you to update it");
					}
					$nested_structure->setUpdateFlag(true);
					if($column->getRelativeSequence() === CONST_BEFORE){
						if($print){
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets updated before this object");
						}
						$that->setPreUpdateForeignDataStructuresFlag(true);
					}else{
						if($print){
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets updated after this object");
						}
						$that->setPostUpdateForeignDataStructuresFlag(true);
					}
				}
				$column->setUpdateFlag(true);
				$nested_structure->setObjectStatus(STATUS_READY_WRITE);
			}else{
				if($print){
					Debug::print("{$f} there was not an existing data structure at column \"{$column_name}\"");
				}
				if($nested_structure->getDeleteFlag()){
					if($print){
						Debug::print("{$f} existing data structure was flagged for deletion before it was inserted into the database. This is only possible if the object was told to apoptose.");
					}
					return STATUS_UNCHANGED;
				}elseif($print){
					Debug::print("{$f} new data structure did not pre-apoptose");
				}
			}
			// flag data foreign data structure for insertion
			if(!$existing || ($existing && $delete)){
				if($print){
					if($delete){
						Debug::print("{$f} an existing data structure \"{$column_name}\" needs to be deleted");
					}else{
						Debug::print("{$f} no existing data structure \"{$column_name}\" to delete");
					}
				}
				// generate key if necessary
				$status = SUCCESS;
				$keygen = $nested_structure->getKeyGenerationMode();
				if($keygen == KEY_GENERATION_MODE_NATURAL){
					if($print){
						Debug::print("{$f} subordinate {$subclass} has a natural key");
					}
				}elseif($nested_structure->hasIdentifierValue()){
					if($print){
						Debug::print("{$f} subordinate structure already has a key");
					}
				}else{
					$status = $nested_structure->generateKey();
					if($status === ERROR_KEY_COLLISION){
						if($print){
							Debug::print("{$f} key collision detected; skipping insertion");
						}
						$key = $nested_structure->getIdentifierValue();
						$nested_structure = registry()->getRegisteredObjectFromKey($key);
					}elseif($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} generateKey returned error status \"{$err}\"");
					}elseif($print){
						Debug::print("{$f} no key collision detected; setting insert flag");
					}
				}
				// mark for insertion, unless it's apoptotic
				if($nested_structure->getDeleteFlag()){
					if($print){
						Debug::print("{$f} subordinate structure was already flagged for deletion");
					}
					$nested_structure->setDeleteFlag(false);
					if($existing){
						if($multiple){
							$that->ejectOldDataStructureListMember($column_name, $old_struct->getIdentifierValue());
						}else{
							$that->ejectOldDataStructure($column_name);
						}
					}
				}else{
					$nested_structure->setInsertFlag(true);
					if($column->getRelativeSequence() === CONST_BEFORE){
						if($print){
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets inserted before this object");
						}
						$that->setPreInsertForeignDataStructuresFlag(true);
					}else{
						if($print){
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets inserted after this object");
						}
						$that->setPostInsertForeignDataStructuresFlag(true);
					}
					$nested_structure->setObjectStatus(STATUS_READY_WRITE);
				}
			}elseif($print){
				Debug::print("{$f} foreign data structure \"{$column_name}\" will not get inserted today");
			}
			// set reference to foreign data structure
			if($multiple){
				if($print){
					Debug::print("{$f} about to set foreign data structure list member at column name \"{$column_name}\"");
				}
				$that->setForeignDataStructureListMember($column_name, $nested_structure);
			}else{
				if($print){
					Debug::print("{$f} about to set foreign data structure at column name \"{$column_name}\"");
				}
				$that->setForeignDataStructure($column_name, $nested_structure);
			}
			if($print){
				Debug::print("{$f} returning normally (success)");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}