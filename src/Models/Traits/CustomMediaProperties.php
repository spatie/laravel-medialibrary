<?php

namespace Spatie\MediaLibrary\Models\Traits;

trait CustomMediaProperties
{
	public function setCustomHeaders(array $customHeaders): self
	{
		$this->setCustomProperty('custom_headers', $customHeaders);

		return $this;
	}

	public function getCustomHeaders($type): array
	{
		if (isset($type)) {
			return ['ACL' => 'public-read'];
		}

		return $this->getCustomProperty('custom_headers', []);
	}
}
