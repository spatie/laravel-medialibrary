<?php

namespace Spatie\MediaLibrary\MediaCollections\Models\Concerns;

trait CustomMediaProperties
{
    /**
     * @return $this
     */
    public function setCustomHeaders(array $customHeaders): self
    {
        $this->setCustomProperty('custom_headers', $customHeaders);

        return $this;
    }

    public function getCustomHeaders(): array
    {
        return $this->getCustomProperty('custom_headers', []);
    }
}
