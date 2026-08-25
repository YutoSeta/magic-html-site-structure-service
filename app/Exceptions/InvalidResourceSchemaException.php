<?php

namespace App\Exceptions;

use Exception;

final class InvalidResourceSchemaException extends Exception
{
    /** @param array<string,mixed> $errors */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('The resource value does not satisfy its JSON Schema.');
    }
}
