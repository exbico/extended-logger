<?php

declare(strict_types=1);

namespace Exbico\ExtendedLogger\Tests\Mock;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

final class PsrLoggerMock implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var array<string, int>
     */
    private array $calls = [];
    public string|Stringable|null $message = null;

    /** @var array<string, mixed>|null $context */
    public ?array $context = null;

    /**
     * @param string $level
     * @param Stringable|string $message
     * @param array<string, mixed> $context
     * @return void
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->calls[$level] ??= 0;
        $this->calls[$level]++;
        $this->message = $message;
        $this->context = $context;
    }

    public function getCallsCount(string $method): int
    {
        return $this->calls[$method] ?? 0;
    }
}
