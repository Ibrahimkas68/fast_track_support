<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class UrgencyProviderException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly string $category)
    {
        parent::__construct('Le service d’analyse n’a pas fourni de résultat exploitable.');
    }
}
