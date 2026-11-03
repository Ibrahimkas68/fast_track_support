<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class TicketStorageException extends RuntimeException implements ShouldntReport {}
