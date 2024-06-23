<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatumInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeReleaseAllForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use Exception;

trait NestedFormsTrait{
	
	public final function getNestedForm(string $column_name, $ds): AjaxForm{
		$f = __METHOD__;
		$print = false;
		if($print){
			if($ds->hasIdentifierValue()){
				Debug::print("{$f} struct identifier is " . $ds->getIdentifierValue());
			}else{
				Debug::print("{$f} struct lacks an identifier");
			}
		}
		$form_class = $this->getInputClass($column_name);
		if($print){
			Debug::print("{$f} column \"{$column_name}\" maps to a form of class \"{$form_class}\"");
		}
		$mode = $this->getTemplateFlag() ? ALLOCATION_MODE_FORM_TEMPLATE : ALLOCATION_MODE_FORM;
		$form = new $form_class($mode);
		if($this->getAllocationMode() === ALLOCATION_MODE_TEMPLATE){
			$form->setTemplateFlag(true);
		}
		if(!$form->hasActionAttribute() && $this->hasActionAttribute()){
			$form->setActionAttribute($this->getActionAttribute());
		}
		$form->setSuperiorFormIndex($column_name);
		$form->setSuperiorForm($this);
		$form->bindContext($ds);
		$this->pushNestedForm($form);
		return $form;
	}
	
	public function pushNestedForm(...$forms):int{
		return $this->pushArrayProperty("nestedForms", ...$forms);
	}
	
	private function subindexNestedInputHelper(&$input, string $super_index):void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(is_array($input)){
			if($print){
				Debug::print("{$f} input is an array, calling recursively");
			}
			foreach($input as $subinput){
				if($subinput instanceof AjaxForm){
					if($print){
						$decl = $subinput->getDeclarationLine();
						Debug::print("{$f} nested input is an AjaxForm, instantiated {$decl}");
					}
					foreach($subinput->getInputs() as $subsubinput){
						$this->subindexNestedInputHelper($subsubinput, $super_index);
					}
					continue;
				}
				$this->subindexNestedInputHelper($subinput, $super_index);
			}
			return;
		}elseif($print){
			$input_class = $input->getClass();
			Debug::print("{$f} about to call {$input_class}->subindexNameAttribute({$super_index})");
		}
		if($input instanceof AjaxForm){
			Debug::error("{$f} input is an AjaxForm");
		}
		$this->subindexNestedInput($input, $super_index);
	}
	
	/**
	 * This function is called by subindexNestedInputHelper.
	 * Override to change subindexing behavior for this form.
	 *
	 * @param InputlikeInterface|AjaxForm|array $input
	 * @param string $super_index
	 * @return string
	 */
	
	protected function subindexNestedInput(InputlikeInterface &$input, string $super_index):void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$reindex = $input->subindexNameAttribute($super_index);
		if($print){
			Debug::print("{$f} set input name attribute to \"{$reindex}\"");
		}
	}
	
	/**
	 * generate subordinate form(s) for index $column_name bound to foreign data structure(s) $structs, subindex the
	 * inputs contained therein, and optionally stick them into a subcontainer
	 *
	 * @param Datum $datum
	 * @param DataStructure[] $structs
	 */
	public final function subindexNestedInputs(Datum $datum, $structs): array{
		$f = __METHOD__;
		try{
			$print = false;
			$ret = [];
			$mode = $this->getAllocationMode();
			$column_name = $datum->getName();
			$multiple = false;
			if($datum instanceof KeyListDatum || ($datum instanceof VirtualDatum && $datum->getReturnType() === TYPE_ARRAY)){
				$multiple = true;
			}
			$struct_num = 0;
			$total_count = count($structs);
			foreach($structs as $struct_id => $subordinate_struct){
				if($multiple){
					if($print){
						Debug::print("{$f} struct ID is \"{$struct_id}\"");
					}
					$concat = new ConcatenateCommand($column_name, '[', $struct_id, ']');
					$super_index = $concat->evaluate();
					deallocate($concat);
				}else{
					if($print){
						Debug::print("{$f} datum class is a ForeignKeyDatum");
					}
					$super_index = $column_name;
				}
				$subordinate_form = $this->getNestedForm($column_name, $subordinate_struct);
				if($subordinate_form instanceof RepeatingFormInterface){
					if(!$subordinate_form->hasIterator()){
						if($subordinate_struct->hasIterator()){
							$subordinate_form->setIterator($subordinate_struct->getIterator());
						}else{
							$subordinate_form->setIterator($struct_num);
						}
					}
					if($struct_num === $total_count - 1){
						$subordinate_form->setLastChildFlag(true);
					}elseif($print){
						Debug::print("{$f} struct #{$struct_num} is not the last child of {$total_count}");
					}
				}
				$sfc = $subordinate_form->getClass();
				if($print){
					Debug::print("{$f} about to get input map for subordinate form of class \"{$sfc}\"");
				}
				if($subordinate_form->hasInputs()){
					if($print){
						Debug::print("{$f} inputs were already generated");
					}
					$subordinate_map = $subordinate_form->getInputs();
				}else{
					if($print){
						Debug::print("{$f} generating inputs now");
					}
					$subordinate_map = $subordinate_form->generateInputs($subordinate_struct);
				}
				if($subordinate_struct->hasIdentifierValue()){
					if($print){
						$fdsc = $subordinate_struct->getClass();
						Debug::print("{$f} foreign data structure of class \"{$fdsc}\" has an identifier value");
					}
					$key_input = new HiddenInput($mode);
					$name = $subordinate_struct->getIdentifierName();
					$key_input->setColumnName($name);
					$key_input->setNameAttribute($name);
					$key_input->setValueAttribute($subordinate_struct->getIdentifierValue());
					$subordinate_form->reconfigureInput($key_input);
					$subordinate_map[$name] = $key_input;
				}
				if($print){
					Debug::print("{$f} about to print inputs generated from {$sfc}");
					foreach($subordinate_map as $input_name => $input){
						$gottype = is_object($input) ? $input->getClass() : gettype($input);
						Debug::print("{$f} {$input_name}: {$gottype}");
					}
				}
				foreach($subordinate_map as $name => $input){
					if($print){
						Debug::print("{$f} about to subindex input with name \"{$name}\"");
					}
					if(is_array($input)){
						if($print){
							Debug::print("{$f} generated an array for index \"{$name}\"");
						}
						$this->subindexNestedInputHelper($input, $super_index);
					}elseif($input instanceof InputlikeInterface){
						$input_class = $input->getClass();
						if(!$input->hasNameAttribute()){
							$did = $input->getDebugId();
							$decl = $input->getDeclarationLine();
							Debug::error("{$f} {$input_class} input \"{$column_name}\" with debug ID \"{$did}\" lacks a name attribute; constructed {$decl}");
						}
						if($print){
							Debug::print("{$f} about to call subindexNestedInputHelper(input, {$super_index})");
						}
						$this->subindexNestedInputHelper($input, $super_index);
						$input->setForm($subordinate_form); //this);
					}elseif($print){
						Debug::error("{$f} subordinate container lacks child nodes, nothing to reindex");
					}elseif($print){
						Debug::print("{$f} subordinate form is its own input container (676)");
					}
					if(is_array($input)){
						if($print){
							Debug::printArray(array_keys($subordinate_map));
							Debug::print("{$f} input \"{$name}\" generated an array");
						}
					}elseif(!$input instanceof InputlikeInterface){
						$decl = $input->getDeclarationLine();
						Debug::error("{$f} input is not an InputlikeInterface; it was declared {$decl}");
					}elseif($print){
						Debug::print("{$f} pushing input for column \"{$column_name}\"");
					}
				}
				$subordinate_form->setInputs($subordinate_map);
				$ret[$struct_num++] = $subordinate_form; // subordinate_map;
			}
			if($print){
				Debug::print("{$f} returning the following array:");
				foreach($ret as $num => $element){
					Debug::print("{$f} {$num} : " . $element->getClass());
				}
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * helper function for generateInputs().
	 * Generates inputs from subordinate forms and adds them to the array.
	 * Also handles input subcontainers
	 *
	 * @param array $inputs
	 * @param Datum $datum
	 * @param string $subcontainer_name
	 * @param array $subcontainers
	 */
	private function generateNestedInputs(Datum $datum): ?array{
		$f = __METHOD__;
		try{
			$context = $this->getContext();
			$column_name = $datum->getName();
			$print = false && $this->getDebugFlag();
			//$dealloc = [];
			if($context->hasForeignDataStructure($column_name)){
				if($print){
					Debug::print("{$f} context already has a subordinate data structure at index \"{$column_name}\"");
				}
				if($datum instanceof VirtualDatum){
					if($print){
						Debug::print("{$f} datum \"{$column_name}\" is virtual");
					}
					if($datum->hasReturnType() && $datum->getReturnType() === TYPE_ARRAY){
						$structs = $context->getVirtualForeignDataStructureList($column_name);
					}else{
						$structs = [$context->getVirtualForeignDataStructure($column_name)];
					}
				}elseif($datum instanceof KeyListDatum){
					if($print){
						Debug::print("{$f} subordinate forms iterating over KeyListDatum");
					}
					$structs = [];
					$unchecked_structs = $context->hasForeignDataStructureList($column_name) ? $context->getForeignDataStructureList($column_name) : [];
					$count = count($unchecked_structs);
					if($print){
						Debug::print("{$f} {$count} unchecked structs");
					}
					foreach($unchecked_structs as $temp_struct){
						if($temp_struct->hasIdentifierValue()){
							$identifier = $temp_struct->getIdentifierValue();
							if($print){
								Debug::print("{$f} assigning structure with identifier \"{$identifier}\"");
							}
							$structs[$identifier] = $temp_struct;
						}else{
							if($print){
								Debug::print("{$f} structure does not have an identifier, pushing to the end of array");
							}
							array_push($structs, $temp_struct);
						}
					}
					$count = count($structs);
					if($print){
						Debug::print("{$f} array contains {$count} objects");
					}
				}elseif($datum instanceof ForeignKeyDatum){
					$structs = [$context->getForeignDataStructure($column_name)];
				}else{
					Debug::error("{$f} neither of the above");
				}
			}else{
				if($print){
					Debug::print("{$f} context ".$context->getDebugString()." does not have a foreign data structure at index \"{$column_name}\"");
				}
				$form_class = $this->getInputClass($column_name);
				if($form_class::getNewFormOption()){
					if($print){
						Debug::print("{$f} about to get foreign data structure class for column \"{$column_name}\"");
					}
					if(!$datum instanceof ForeignKeyDatumInterface){
						$context_class = $context->getShortClass();
						Debug::error("{$f} column \"{$column_name}\" is not a foreign key datum for context of class \"{$context_class}\"");
					}
					$subordinate_class = $datum->getForeignDataStructureClass($context);
					$subordinate_struct = new $subordinate_class();
					if($print){
						Debug::print("{$f} subordinate data structure class is \"{$subordinate_class}\"");
					}
					if($context->hasColumn($column_name)){
						$column = $context->getColumn($column_name);
						if($column->hasConverseRelationshipKeyName()){//XXX added this conditional during memory optimization
							$converse = $column->getConverseRelationshipKeyName();
							$context->subordinateForeignDataStructure($column_name, $subordinate_struct, $converse);
							$converse_column = $subordinate_struct->getColumn($converse);
							if($subordinate_struct->hasColumn($converse)){
								if($column instanceof ForeignKeyDatum){
									if($print){
										Debug::print("{$f} column {$column_name} is a foreign key datum");
									}
									$context->setForeignDataStructure($column_name, $subordinate_struct);
									if($context->canReleaseForeignDataStructure($column_name)){
										$context->releaseForeignDataStructure($column_name);
									}
								}elseif($column instanceof KeyListDatum){
									if($print){
										Debug::print("{$f} column {$column_name} is a foreign key datum");
									}
									$context->setForeignDataStructureListMember($column_name, $subordinate_struct);
									$key2 = $subordinate_struct->ejectIdentifierValue();
									if($context->canReleaseForeignDataStructureListMember($column_name, $key2)){
										if($print){
											Debug::print("{$f} releasing {$column_name} list member with key \"{$key2}\"");
										}
										$context->releaseForeignDataStructureListMember($column_name, $key2);
									}elseif($print){
										Debug::print("{$f} we cannot release {$column_name} list member \"{$key2}\"");
									}
								}else{
									Debug::print("{$f} column \"{$column_name}\" is neither a ForeignKey nor KeyListDatum, skipping setting it as a relationship");
								}
								if(
									$converse_column instanceof ForeignKeyDatum &&
									$subordinate_struct->canReleaseForeignDataStructure($converse)
								){
									$random1 = sha1(random_bytes(32));
									$random2 = sha1(random_bytes(32));
									//for the foreign data structure, before releasing all other foreign data structures, we release this one and tell it to deallocate regardless of whether it is flagged to do so
									$closure1 = function(BeforeReleaseAllForeignDataStructuresEvent $event, DataStructure $target)
									use ($converse, $random2){
										$target->removeEventListener($event);
										if($target->hasEventListener(EVENT_RELEASE_FOREIGN, $random2)){
											$target->removeEventListener(EVENT_RELEASE_FOREIGN, $random2);
										}
										if($target->canReleaseForeignDataStructure($converse)){
											$target->releaseForeignDataStructure($converse, $event->getProperty("recursive"));
										}
									};
									$subordinate_struct->addEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $closure1, $random1);
									//for the foreign data structure, if the host is released before the BeforeReleaseAllFOreignDataStructuresEvent fires, clean up and prevent it from firing
									$closure2 = function(ReleaseForeignDataStructureEvent $event, DataStructure $target) 
									use ($converse, $random1){
										if($event->getForeignKey() === $converse){
											$target->removeEventListener($event);
											if($target->hasEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $random1)){
												$target->removeEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $random1);
											}
										}
									};
									$subordinate_struct->addEventListener(EVENT_RELEASE_FOREIGN, $closure2, $random2);
								}elseif($converse_column instanceof KeyListDatum){//cannot determine eligibility until the key is generated
									$random1 = sha1(random_bytes(32));
									$random2 = sha1(random_bytes(32));
									$closure3 = function(BeforeReleaseAllForeignDataStructuresEvent $event, DataStructuralTrait $target)
									use ($context, $converse, $random2){
										$f = __METHOD__;
										$target->removeEventListener($event);
										if($target->hasEventListener(EVENT_RELEASE_FOREIGN, $random2)){
											$target->removeEventListener(EVENT_RELEASE_FOREIGN, $random2);
										}
										if($context->hasIdentifierValue()){
											$key = $context->getIdentifierValue();
											if($target->canReleaseForeignDataStructureListMember($converse, $key)){
												$target->releaseForeignDataStructureListMember($converse, $key);
											}
										}else{
											Debug::error("{$f} context lacks an identifier");
										}
									};
									$subordinate_struct->addEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $closure3, $random1);
									$closure4 = function(ReleaseForeignDataStructureEvent $event, DataStructuralTrait $target)
									use ($context, $converse, $random1){
										$f = __METHOD__;
										if(!$context->hasIdentifierValue()){
											Debug::error("{$f} context lacks an identifier");
										}
										$key = $context->getIdentifierValue();
										if(
											$event->getColumnName() === $converse &&
											$event->getForeignKey() === $key
											){
												$target->removeEventListener($event);
												if($target->hasEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $random1)){
													$target->removeEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN, $random1);
												}
										}
									};
									$subordinate_struct->addEventListener(EVENT_RELEASE_FOREIGN, $closure4, $random2);
								}elseif($print){
									Debug::print("{$f} foreign data structure's converse column \"{$converse}\" is neither a ForeignKeyDatum nor KeyListDatum and/or it cannot release the host data structure because it is flagged as a parentKey column");
								}
							}
						}elseif($print){
							Debug::print("{$f} column \"{$column_name}\" does not have a converse relationship key name");
						}
					}elseif($print){
						Debug::print("{$f} context does not have a column \"{$column_name}\"");
					}
					$structs = [$subordinate_struct];
				}else{
					if($print){
						Debug::print("{$f} form class \"{$form_class}\" does not allow new forms when foreign data structures for that column do not exist. This is a ".$this->getDebugString()." with context ".$context->getDebugString());
					}
					$structs = null;
				}
			}
			// iterate through data structures (only 1 for ForeignKeyDatum indices)
			if(isset($structs) && is_array($structs) && !empty($structs)){
				return $this->subindexNestedInputs($datum, $structs);
			}
			return null;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}