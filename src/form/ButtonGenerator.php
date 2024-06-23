<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use Exception;

abstract class ButtonGenerator{
	
	public static function generate(AjaxForm $that, string $directive, $value = null):ButtonInput{
		$f = __METHOD__;
		try{
			$print = false && $that->getDebugFlag();
			$mode = $that->getAllocationMode();
			$button = new ButtonInput($mode);
			if($value !== null){
				$button->setValueAttribute($value);
				$button->setNameAttribute(new ConcatenateCommand("directive", "[", $directive, "]"));
			}else{
				$button->setNameAttribute("directive");
				$button->setValueAttribute($directive);
			}
			if(!$that->getTemplateFlag()){
				$form_id = $that->getIdAttribute();
				$id = "{$directive}-{$form_id}";
				if($value !== null){
					$id .= "-" . NameDatum::normalize($value);
				}
				$button->setIdAttribute($id);
			}
			$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
			$button->setTypeAttribute("submit");
			$button->setForm($that);
			$context = $that->hasContext() ? $that->getContext() : null;
			if(
				$context !== null &&
				is_object($context) &&
				method_exists($context, "getPrettyClassName")
			){
				$pretty = $context->getPrettyClassName();
			}else{
				$pretty = null;
			}
			switch($directive){
				case DIRECTIVE_IMPORT_CSV:
					$innerHTML = _("Import CSV files");
					break;
				case DIRECTIVE_INSERT:
					$innerHTML = $pretty ? substitute(_("Insert %1%"), $pretty) : _("Insert");
					break;
				case DIRECTIVE_REGENERATE:
				case DIRECTIVE_UNSET:
				case DIRECTIVE_UPDATE:
					$innerHTML = $pretty ? substitute(_("Update %1%"), $pretty) : _("Update");
					break;
				case DIRECTIVE_DELETE:
				case DIRECTIVE_DELETE_FOREIGN:
				case DIRECTIVE_MASS_DELETE:
					$innerHTML = $pretty ? substitute(_("Delete %1%"), $pretty) : _("Delete");
					break;
				case DIRECTIVE_DOWNLOAD:
					$innerHTML = $pretty ? substitute(_("Download %1%"), $pretty) : _("Download");
					break;
				case DIRECTIVE_EMAIL_CONFIRMATION_CODE:
					$innerHTML = _("Send confirmation code");
					break;
				case DIRECTIVE_GENERATE:
					$innerHTML = $pretty ? substitute(_("Generate %1%"), $pretty) : _("Generate");
					break;
				case DIRECTIVE_PROCESS:
					$innerHTML = $pretty ? substitute(_("Process %1%"), $pretty) : _("Process");
					break;
				case DIRECTIVE_READ:
				case DIRECTIVE_READ_MULTIPLE:
					$innerHTML = $pretty ? substitute(_("Read %1%"), $pretty) : _("Read");
					break;
				case DIRECTIVE_REFRESH_SESSION:
					$innerHTML = _("Refresh session");
					break;
				case DIRECTIVE_SEARCH:
					$innerHTML = _("Search");
					break;
				case DIRECTIVE_SELECT:
					$innerHTML = $pretty ? substitute(_("Select %1%"), $pretty) : _("Select");
					break;
				case DIRECTIVE_MFA:
				case DIRECTIVE_SUBMIT:
					$innerHTML = _("Submit");
					break;
				case DIRECTIVE_UPLOAD:
					$innerHTML = _("Upload");
					break;
				case DIRECTIVE_VALIDATE:
					$innerHTML = _("Validate");
					break;
				default:
					if($print){
						Debug::warning("{$f} invalid directive \"{$directive}\"");
					}
					return $button;
			}
			$button->setInnerHTML($innerHTML);
			return $button;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}