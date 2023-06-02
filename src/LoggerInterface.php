<?php

declare(strict_types=1);

namespace Exbico\ExtendedLogger;

use Throwable;

interface LoggerInterface
{
    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function emergency(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function alert(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function critical(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function error(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function warning(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function notice(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function info(string|Throwable $source, array $context = []): void;

    /**
     * @param string|Throwable $source
     * @param array<string, mixed> $context
     * @return void
     */
    public function debug(string|Throwable $source, array $context = []): void;
}
