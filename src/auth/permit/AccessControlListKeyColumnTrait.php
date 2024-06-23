<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

trait AccessControlListKeyColumnTrait{
	
	public function getAccessControlListKey(): ?string{
		return $this->getColumnValue("accessControlListKey");
	}

	public function setAccessControlListKey(?string $value): ?string{
		return $this->setColumnValue("accessControlListKey", $value);
	}

	public function hasAccessControlListKey(): bool{
		return $this->hasColumnValue("accessControlListKey");
	}

	public function ejectAccessControlListKey(): ?string{
		return $this->ejectColumnValue("accessControlListKey");
	}

	public function hasAccessControlListData(): bool{
		return $this->hasForeignDataStructure("accessControlListKey");
	}

	public function getAccessControlListData(): ?AccessControlListData{
		return $this->getForeignDataStructure("accessControlListKey");
	}

	public function setAccessControlListData(?AccessControlListData $fds): ?AccessControlListData{
		return $this->setForeignDataStructure("accessControlListKey", $fds);
	}
}
