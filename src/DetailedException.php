<?php

declare(strict_types=1);

namespace Exbico\ExtendedLogger;

use RuntimeException;
use Throwable;

abstract class DetailedException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        /** @var array<string, mixed> $details */
        private array $details = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    final public function getDetails(): array
    {
        return $this->details;
    }
}
