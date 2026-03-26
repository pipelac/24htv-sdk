<?php

namespace TwentyFourTv\Tests\Unit\Exception;

use TwentyFourTv\Exception\ConnectionException;
use TwentyFourTv\Exception\NotFoundException;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionHierarchyTest extends TestCase
{
    /**
     * @dataProvider exceptionProvider
     */
    public function testExceptionInheritsFromBase($exceptionClass)
    {
        $exception = new $exceptionClass('Test error', 0, 400, ['detail' => 'error']);

        $this->assertInstanceOf(
            'TwentyFourTv\Exception\TwentyFourTvException',
            $exception,
            "{$exceptionClass} should extend TwentyFourTvException",
        );
        $this->assertInstanceOf('\Exception', $exception);
    }

    public static function exceptionProvider()
    {
        return [
            'AuthenticationException' => ['TwentyFourTv\Exception\AuthenticationException'],
            'ConflictException'       => ['TwentyFourTv\Exception\ConflictException'],
            'ConnectionException'     => ['TwentyFourTv\Exception\ConnectionException'],
            'ForbiddenException'      => ['TwentyFourTv\Exception\ForbiddenException'],
            'NotFoundException'       => ['TwentyFourTv\Exception\NotFoundException'],
            'RateLimitException'      => ['TwentyFourTv\Exception\RateLimitException'],
            'ValidationException'     => ['TwentyFourTv\Exception\ValidationException'],
        ];
    }

    public function testBaseExceptionProperties()
    {
        $exception = new TwentyFourTvException(
            'API error',
            0,
            422,
            ['detail' => 'Invalid field'],
            null,
            'POST',
            '/users',
        );

        $this->assertEquals('API error', $exception->getMessage());
        $this->assertEquals(422, $exception->getHttpCode());
        $this->assertEquals(['detail' => 'Invalid field'], $exception->getResponseBody());
        $this->assertEquals('POST', $exception->getMethod());
        $this->assertEquals('/users', $exception->getEndpoint());
    }

    public function testBaseExceptionNullDefaults()
    {
        $exception = new TwentyFourTvException('Simple error');

        $this->assertEquals('Simple error', $exception->getMessage());
        $this->assertNull($exception->getHttpCode());
        $this->assertNull($exception->getResponseBody());
        $this->assertNull($exception->getMethod());
        $this->assertNull($exception->getEndpoint());
    }

    public function testExceptionChaining()
    {
        $previous = new \RuntimeException('Connection failed');
        $exception = new ConnectionException(
            'API unavailable',
            0,
            null,
            null,
            $previous,
        );

        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals('Connection failed', $exception->getPrevious()->getMessage());
    }

    public function testChildExceptionPreservesHttpCode()
    {
        $exception = new NotFoundException('User not found', 0, 404, ['id' => 42]);

        $this->assertEquals(404, $exception->getHttpCode());
        $this->assertEquals(['id' => 42], $exception->getResponseBody());
    }

    public function testCatchByBaseType()
    {
        $caught = false;

        try {
            throw new ValidationException('Bad request', 0, 400, null);
        } catch (TwentyFourTvException $e) {
            $caught = true;
            $this->assertEquals(400, $e->getHttpCode());
        }

        $this->assertTrue($caught, 'ValidationException should be catchable as TwentyFourTvException');
    }
}
