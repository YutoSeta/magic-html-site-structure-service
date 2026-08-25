<?php

namespace App\Exceptions;

use Exception;

final class MissingMediaReferenceException extends Exception
{
    /** @param list<string> $mediaRefs */
    public function __construct(public readonly array $mediaRefs)
    {
        parent::__construct('One or more media references do not exist in this site.');
    }
}
