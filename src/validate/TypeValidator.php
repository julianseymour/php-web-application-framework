<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\is_base64;
use function JulianSeymour\PHPWebApplicationFramework\is_sha1;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\validateTableName;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\VariadicExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Closure;
use Exception;

class TypeValidator extends Validator{

	public function evaluate(&$validate_me): int{
		ErrorMessage::unimplemented(f(static::class));
	}

	public static function validateType($value, $check){
		$f = __METHOD__;
		try{
			$print = false;
			if($check instanceof Closure){
				if($print){
					Debug::print("{$f} calling a closure");
				}
				return $check($value) ? SUCCESS : FAILURE;
			}elseif(is_bool($check)){
				return is_bool($check) ? SUCCESS : FAILURE;
			}elseif(is_int($check)){
				return is_int($value) ? SUCCESS : FAILURE;
			}elseif(is_double($check)){
				return is_double($value) ? SUCCESS : FAILURE;
			}elseif(is_float($check)){
				return is_float($value) ? SUCCESS : FAILURE;
			}elseif(is_string($check)){
				if($value === null){
					if(starts_with($check, "?")){
						return SUCCESS;
					}
					return FAILURE;
				}elseif(starts_with($check, "?")){
					$check = str_replace("?", "", $check);
					if($print){
						Debug::print("{$f} validator is \"{$check}\"");
					}
				}
				$lower = strtolower($check);
				switch($lower){
					case "a":
					case "arr":
					case "array":
						return (is_array($value) && !empty($value)) ? SUCCESS : FAILURE;
					case "b":
					case "bool":
					case "boolean":
						return is_bool($value) ? SUCCESS : FAILURE;
					case "base64":
						return is_base64($value) ? SUCCESS : FAILURE;
					case "class":
						return is_string($value) && class_exists($value) ? SUCCESS : FAILURE;
					case "d":
					case "double":
						return (is_double($value) || is_float($value) || is_int($value)) ? SUCCESS : FAILURE;
					case "f":
					case "float":
						return (is_float($value) || is_int($value)) ? SUCCESS : FAILURE;
					case "hex":
					case "x":
						return ctype_xdigit($value) ? SUCCESS : FAILURE;
					case "i":
					case "int":
					case "integer":
						return is_int($value) ? SUCCESS : FAILURE;
					case "o":
					case "obj":
					case "object":
						return is_object($value) ? SUCCESS : FAILURE;
					case "s":
					case "str":
					case "string":
						return (is_string($value) && !empty($value)) ? SUCCESS : FAILURE;
					case "table":
						return validateTableName($value) ? SUCCESS : FAILURE;
					case "sha1":
						return is_sha1($value) ? SUCCESS : FAILURE;
					default:
						if(class_exists($check) || interface_exists($check)){
							if($print){
								$gottype = gettype($value);
								Debug::print("{$f} check is a class name \"{$check}\"");
								if($value instanceof $check){
									Debug::print("{$f} yes, value is an instanceof \"{$check}\"");
								}else{
									
									Debug::print("{$f} no, {$gottype} value is not an instanceof \"{$check}\"");
								}
							}
							if(is_object($value)){
								return $value instanceof $check ? SUCCESS : FAILURE;
							}elseif(is_string($value)){
								return is_a($value, $check, true) ? SUCCESS : FAILURE;
							}elseif($print){
								Debug::print("{$f} value is a {$gottype}, and fails type check \"{$check}\'");
							}
							return FAILURE;
						}else{
							Debug::error("{$f} class \"{$check}\" does not exist");
						}
				}
			}elseif(is_object($check)){
				if($check instanceof VariadicExpressionCommand){
					$expressions = $check->getParameters();
					if($check instanceof AndCommand){
						foreach($expressions as $expr){
							$status = TypeValidator::validateType($value, $expr);
							if($status !== SUCCESS){
								return $status;
							}
						}
						return SUCCESS;
					}elseif($check instanceof OrCommand){
						if($print){
							Debug::print("{$f} check is an or expression");
						}
						foreach($expressions as $expr){
							$status = TypeValidator::validateType($value, $expr);
							if($status === SUCCESS){
								if($print){
									Debug::print("{$f} returning SUCCESS");
								}
								return $status;
							}
						}
						if($print){
							Debug::print("{$f} or expression failed");
						}
						return FAILURE;
					}else{
						$class = $check->getClass();
						Debug::error("{$f} invalid variadic expression class \"{$class}\"");
					}
				}else{
					$class = $check->getClass();
					Debug::error("{$f} invalid class \"{$class}\"");
				}
			}elseif(is_array($check)){
				if(!is_array($value)){
					return FAILURE;
				}
				if(!empty($check)){
					if(count($check) > 1){
						Debug::error("{$f} if you want to validate an array of X, the array you pass for the filter ID must contain only one element");
					}
					$check = $check[0];
					foreach($value as $sub){
						if(static::validateType($sub, $check) !== SUCCESS){
							return FAILURE;
						}
					}
				}
				return SUCCESS;
			}
			return FAILURE;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
