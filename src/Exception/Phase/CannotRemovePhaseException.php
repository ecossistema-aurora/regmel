<?php

declare(strict_types=1);

namespace App\Exception\Phase;

use LogicException;
use Throwable;

final class CannotRemovePhaseException extends LogicException
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        $message = 'Cannot remove this phase.';
        parent::__construct($message, $code, $previous);
    }
}
