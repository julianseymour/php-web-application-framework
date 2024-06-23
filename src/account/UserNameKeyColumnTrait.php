<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

trait UserNameKeyColumnTrait{

	public function hasUsernameKey(): bool{
		return $this->hasColumnValue("userNameKey");
	}

	public function setUsernameKey(string $value): string{
		return $this->setColumnValue("userNameKey", $value);
	}

	public function getUsernameKey(): ?string{
		return $this->getColumnValue("userNameKey");
	}

	public function ejectUsernameKey(): ?string{
		return $this->ejectColumnValue("userNameKey");
	}

	public function hasUsernameData(): bool{
		return $this->hasForeignDataStructure("userNameKey");
	}

	public function setUsernameData(UsernameData $struct): UsernameData{
		return $this->setForeignDataStructure("userNameKey", $struct);
	}

	public function getUsernameData(): UsernameData{
		return $this->getForeignDataStructure("userNameKey");
	}

	public function ejectUsernameData(): ?UsernameData{
		return $this->ejectForeignDataStructure("userNameKey");
	}
}
