<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\group;

trait GroupKeyColumnTrait{

	public function getGroupKey(): ?string{
		return $this->getColumnValue("groupKey");
	}

	public function hasGroupKey(): bool{
		return $this->hasColumnValue("groupKey");
	}

	public function setGroupKey(?string $value): ?string{
		return $this->setColumnValue("groupKey", $value);
	}

	public function hasGroupData(): bool{
		return $this->hasForeignDataStructure("groupKey");
	}

	public function setGroupData(?GroupData $group): ?GroupData{
		return $this->setForeignDataStructure("groupKey", $group);
	}

	public function getGroupData(): ?GroupData{
		return $this->getForeignDataStructure("groupKey");
	}
}
