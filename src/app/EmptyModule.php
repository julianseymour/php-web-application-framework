<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use mysqli;

class EmptyModule extends Module{

	public function getCascadingStyleSheetFilePaths(): ?array{
		return [];
	}

	public function getClientCommandClasses(): ?array{
		return [];
	}

	public function getClientConstants(): ?array{
		return [];
	}

	public function getClientDataStructureClasses(): ?array{
		return [];
	}

	public function getClientRenderedFormClasses(): ?array{
		return [];
	}

	public function getClientUseCaseDictionary(): ?array{
		return [];
	}

	public function getDataStructureClasses(): ?array{
		return [];
	}

	public function getDebugJavaScriptFilePaths(): ?array{
		return [];
	}

	public function getFormDataSubmissionClasses(): ?array{
		return [];
	}

	public function getGrantArray(): ?array{
		return [];
	}

	public function getInvokeableJavaScriptFunctions(): ?array{
		return [];
	}

	public function getJavaScriptFilePaths(): ?array{
		return [];
	}

	public function getJavaScriptFunctionGeneratorClasses(): ?array{
		return [];
	}

	public function getLegalIntersectionObservers(): ?array{
		return [];
	}

	public function getMessageEventHandlerCases(): ?array{
		return [];
	}

	public function getMiscellaneousClasses(): ?array{
		return [];
	}

	public function getPhpFileInclusionPaths(): ?array{
		return [];
	}

	public function getServiceWorkerDependencyFilePaths(): ?array{
		return [];
	}

	public function getSpecialTemplateClasses(): ?array{
		return [];
	}

	public function getStoredRoutines(): ?array{
		return [];
	}

	public function getTemplateElementClasses(): ?array{
		return [];
	}

	public function getThemeClasses(): ?array{
		return [];
	}

	public function getTypedNotificationClasses(): ?array{
		return [];
	}

	public function getUseCaseDictionary(): ?array{
		return [];
	}

	public function getValidDirectives(): ?array{
		return [];
	}

	public function getValidMimeTypes(): ?array{
		return [];
	}

	public function getValidatorClasses(): ?array{
		return [];
	}

	public function getWidgetClasses(): ?array{
		return [];
	}

	public function afterInstallHook(mysqli $mysqli): int{
		return SUCCESS;
	}

	public function beforeInstallHook(): int{
		return SUCCESS;
	}

	public function getUniversalFormClasses(): ?array{
		return [];
	}

	public function afterAccountCreationHook(mysqli $mysqli, PlayableUser $user): int{
		return SUCCESS;
	}

	public function getPollingUseCaseClasses(): ?array{
		return [];
	}
	
	public function getEmbedName():string{
		return "empty";
	}
	
	public function getModuleSpecificColumns(DataStructure $ds): ?array{
		return [];
	}
}
