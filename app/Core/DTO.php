<?php

namespace App\Core;

abstract class DTO
{
    /**
     * Hydrate a new static DTO instance from an associative array.
     */
    public static function fromArray(array $data): static
    {
        $dto = new static();
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }
        return $dto;
    }

    /**
     * Convert the DTO properties to an associative array.
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
