<?php

namespace App\Contracts;

use App\Data\UrgencyResult;

interface UrgencyAnalyzerInterface
{
    public function analyze(string $description): UrgencyResult;
}
