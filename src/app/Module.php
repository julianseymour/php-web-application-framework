<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use mysqli;

abstract class Module extends Basic{

	public abstract function getCascadingStyleSheetFilePaths():?array;

	public abstract function getClientCommandClasses():?array;

	public abstract function getClientConstants():?array;

	public abstract function getClientDataStructureClasses():?array;

	public abstract function getClientRenderedFormClasses():?array;

	public abstract function getClientUseCaseDictionary():?array;

	public abstract function getDataStructureClasses():?array;

	public abstract function getDebugJavaScriptFilePaths():?array;
	
	public abstract function getEmbedName():string;

	public abstract function getFormDataSubmissionClasses():?array;

	public abstract function getGrantArray():?array;

	public abstract function getInvokeableJavaScriptFunctions():?array;

	public abstract function getJavaScriptFilePaths():?array;

	public abstract function getJavaScriptFunctionGeneratorClasses():?array;

	public abstract function getLegalIntersectionObservers():?array;

	public abstract function getMessageEventHandlerCases():?array;

	public abstract function getMiscellaneousClasses():?array;

	public abstract function getPhpFileInclusionPaths():?array;

	public abstract function getPollingUseCaseClasses():?array;

	public abstract function getServiceWorkerDependencyFilePaths():?array;

	public abstract function getSpecialTemplateClasses():?array;

	public abstract function getStoredRoutines():?array;

	public abstract function getTemplateElementClasses():?array;

	public abstract function getTypedNotificationClasses():?array;

	public abstract function getUniversalFormClasses():?array;

	public abstract function getUseCaseDictionary():?array;

	public abstract function getValidatorClasses():?array;

	public abstract function getWidgetClasses():?array;

	public abstract function getValidMimeTypes():?array;

	public abstract function getValidDirectives():?array;

	public abstract function beforeInstallHook(): int;

	public abstract function afterInstallHook(mysqli $mysqli): int;

	public abstract function afterAccountCreationHook(mysqli $mysqli, PlayableUser $user): int;
	
	public abstract function getModuleSpecificColumns(DataStructure $ds):?array;
	
	public abstract function getInstallDirectories():?array;
	
	public abstract function getContentSecurityPolicyDirectives():?array;
}
