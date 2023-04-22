<?php

declare(strict_types=1);

namespace Exbico\ExtendedLogger;

use Stringable;
use Throwable;

final class Logger implements LoggerInterface
{
    public function __construct(private \Psr\Log\LoggerInterface $logger)
    {
    }

    public function emergency(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->emergency($this->getMessage($source), $this->getContext($source, $context));
    }

    public function alert(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->alert($this->getMessage($source), $this->getContext($source, $context));
    }

    public function critical(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->critical($this->getMessage($source), $this->getContext($source, $context));
    }

    public function error(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->error($this->getMessage($source), $this->getContext($source, $context));
    }

    public function warning(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->warning($this->getMessage($source), $this->getContext($source, $context));
    }

    public function notice(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->notice($this->getMessage($source), $this->getContext($source, $context));
    }

    public function info(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->info($this->getMessage($source), $this->getContext($source, $context));
    }

    public function debug(string|Stringable|Throwable $source, array $context = []): void
    {
        $this->logger->debug($this->getMessage($source), $this->getContext($source, $context));
    }

    private function getMessage(string|Stringable|Throwable $source): string|Stringable
    {
        return $source instanceof Throwable
            ? $source->getMessage()
            : $source;
    }

    /**
     * @param string|Stringable|Throwable $source
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function getContext(string|Stringable|Throwable $source, array $context): array
    {
        if ($source instanceof Throwable) {
            $context['exception'] = $this->getContextForException($source);
        }

        return $context;
    }

    /**
     * @param Throwable $exception
     * @param bool $isNestedException
     * @return array<string, mixed>
     */
    private function getContextForException(Throwable $exception, bool $isNestedException = false): array
    {
        $result = ['class' => $exception::class];

        if ($isNestedException) {
            $result['message'] = $exception->getMessage();
        } else {
            $result['trace'] = $exception->getTraceAsString();
        }

        if ($exception->getPrevious() !== null) {
            $result['previous'] = $this->getContextForException(
                exception        : $exception->getPrevious(),
                isNestedException: true,
            );
        }

        if ($exception instanceof DetailedException) {
            $result = array_merge($result, $exception->getDetails());
        }

        return $result;
    }
}
