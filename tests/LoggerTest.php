<?php

declare(strict_types=1);

namespace Exbico\ExtendedLogger\Tests;

use Exbico\ExtendedLogger\DetailedException;
use Exbico\ExtendedLogger\Logger;
use Exbico\ExtendedLogger\LoggerInterface;
use Exbico\ExtendedLogger\Tests\Mock\DetailedExceptionMock;
use Exbico\ExtendedLogger\Tests\Mock\PsrLoggerMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Stringable;

#[CoversClass(Logger::class)]
final class LoggerTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $logger = new Logger(new PsrLoggerMock());
        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    #[DataProvider('methods')]
    public function testStrings(string $method): void
    {
        $psrLogger = new PsrLoggerMock();

        $logger = new Logger($psrLogger);

        $logger->$method('Test message!', ['testKey' => 'test value']);

        $this->assertEquals('Test message!', $psrLogger->message);
        $this->assertEquals(['testKey' => 'test value'], $psrLogger->context);
        $this->assertEquals(1, $psrLogger->getCallsCount($method));
    }

    #[DataProvider('methods')]
    public function testStringables(string $method): void
    {
        $psrLogger = new PsrLoggerMock();

        $logger = new Logger($psrLogger);

        $stringable = new class implements Stringable {
            public function __toString()
            {
                return 'Test stringable!';
            }
        };

        $logger->$method($stringable, ['testKey' => 'test value']);

        $this->assertEquals('Test stringable!', $psrLogger->message);
        $this->assertEquals(['testKey' => 'test value'], $psrLogger->context);
        $this->assertEquals(1, $psrLogger->getCallsCount($method));
    }

    #[DataProvider('methods')]
    public function testException(string $method): void
    {
        $psrLogger = new PsrLoggerMock();

        $logger = new Logger($psrLogger);

        $exception = new RuntimeException('Exception message!');

        $logger->$method($exception, ['testKey' => 'test value']);

        $exceptionContext = $this->getAsArray($psrLogger->context['exception'] ?? []);
        $this->assertEquals('Exception message!', $psrLogger->message);
        $this->assertEquals('test value', $psrLogger->context['testKey'] ?? null);
        $this->assertEquals(RuntimeException::class, $exceptionContext['class'] ?? null);
        $this->assertArrayHasKey('trace', $exceptionContext);
        $this->assertArrayNotHasKey('previous', $exceptionContext);
        $this->assertArrayNotHasKey('testKey', $exceptionContext);
        $this->assertEquals(1, $psrLogger->getCallsCount($method));
    }

    #[DataProvider('methods')]
    public function testDetailedException(string $method): void
    {
        $psrLogger = new PsrLoggerMock();

        $logger = new Logger($psrLogger);

        $exception = new DetailedExceptionMock(
            message: 'DetailedException message!',
            details: ['DetailedExceptionContext' => 'Context message!'],
        );

        $logger->$method($exception, ['testKey' => 'test value']);

        $this->assertInstanceOf(DetailedException::class, $exception);

        $exceptionContext = $this->getAsArray($psrLogger->context['exception'] ?? []);
        $this->assertEquals('DetailedException message!', $psrLogger->message);
        $this->assertEquals('test value', $psrLogger->context['testKey'] ?? null);
        $this->assertEquals(DetailedExceptionMock::class, $exceptionContext['class'] ?? null);
        $this->assertEquals('Context message!', $exceptionContext['DetailedExceptionContext'] ?? null);
        $this->assertArrayHasKey('trace', $exceptionContext);
        $this->assertArrayNotHasKey('previous', $exceptionContext);
        $this->assertArrayNotHasKey('testKey', $exceptionContext);
        $this->assertEquals(1, $psrLogger->getCallsCount($method));
    }

    #[DataProvider('methods')]
    public function testNestedException(string $method): void
    {
        $psrLogger = new PsrLoggerMock();

        $logger = new Logger($psrLogger);

        $exception = new DetailedExceptionMock(
            message : 'DetailedException message!',
            previous: new RuntimeException('Nested Exception message!'),
            details : ['DetailedExceptionContext' => 'Context message!'],
        );

        $logger->$method($exception, ['testKey' => 'test value']);

        $exceptionContext = $this->getAsArray($psrLogger->context['exception'] ?? []);
        $this->assertEquals('DetailedException message!', $psrLogger->message);
        $this->assertEquals('test value', $psrLogger->context['testKey'] ?? null);
        $this->assertEquals(DetailedExceptionMock::class, $exceptionContext['class'] ?? null);
        $this->assertEquals('Context message!', $exceptionContext['DetailedExceptionContext'] ?? null);
        $this->assertArrayHasKey('trace', $exceptionContext);
        $this->assertArrayNotHasKey('testKey', $exceptionContext);

        $this->assertArrayHasKey('previous', $exceptionContext);
        $this->assertEquals(
            [
                'class'   => RuntimeException::class,
                'message' => 'Nested Exception message!',
            ],
            $exceptionContext['previous'],
        );

        $this->assertEquals(1, $psrLogger->getCallsCount($method));
    }

    #[DataProvider('methods')]
    public function testNestedDetailedExceptions(string $method): void
    {
        $psrLogger = new PsrLoggerMock();

        $logger = new Logger($psrLogger);

        $nestedException = new DetailedExceptionMock(
            message : 'Nested DetailedException message!',
            previous: new RuntimeException('Nested Exception message!'),
            details : ['NestedDetailedExceptionContext' => 'Nested context message!'],
        );

        $exception = new DetailedExceptionMock(
            message : 'DetailedException message!',
            previous: $nestedException,
            details : ['DetailedExceptionContext' => 'Context message!'],
        );

        $logger->$method($exception, ['testKey' => 'test value']);

        $exceptionContext = $this->getAsArray($psrLogger->context['exception'] ?? []);
        $this->assertEquals('DetailedException message!', $psrLogger->message);
        $this->assertEquals('test value', $psrLogger->context['testKey'] ?? null);
        $this->assertEquals(DetailedExceptionMock::class, $exceptionContext['class'] ?? null);
        $this->assertEquals('Context message!', $exceptionContext['DetailedExceptionContext'] ?? null);
        $this->assertArrayHasKey('trace', $exceptionContext);
        $this->assertArrayNotHasKey('testKey', $exceptionContext);
        $this->assertArrayNotHasKey('NestedDetailedExceptionContext', $exceptionContext);

        $previousExceptionContext = $this->getAsArray($exceptionContext['previous'] ?? []);
        $this->assertEquals(DetailedExceptionMock::class, $previousExceptionContext['class'] ?? null);
        $this->assertEquals('Nested DetailedException message!', $previousExceptionContext['message'] ?? null);
        $this->assertEquals(
            'Nested context message!',
            $previousExceptionContext['NestedDetailedExceptionContext'] ?? null,
        );

        $this->assertArrayHasKey('previous', $previousExceptionContext);
        $this->assertEquals(
            [
                'class'   => RuntimeException::class,
                'message' => 'Nested Exception message!',
            ],
            $previousExceptionContext['previous'],
        );

        $this->assertEquals(1, $psrLogger->getCallsCount($method));
    }

    /**
     * @return array<string, list<string>>
     */
    public static function methods(): array
    {
        return [
            'emergency' => ['emergency'],
            'alert'     => ['alert'],
            'critical'  => ['critical'],
            'error'     => ['error'],
            'warning'   => ['warning'],
            'notice'    => ['notice'],
            'info'      => ['info'],
            'debug'     => ['debug'],
        ];
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>
     */
    private function getAsArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }
}
