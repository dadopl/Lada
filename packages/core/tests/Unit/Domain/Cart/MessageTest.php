<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Cart;

use Lada\Core\Domain\Cart\Message;
use Lada\Core\Domain\Cart\Severity;
use Lada\Core\Domain\Exception\InvalidCart;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    #[Test]
    public function it_points_at_the_field_it_is_about(): void
    {
        // Arrange
        $path = '$.line_items[0]';

        // Act
        $message = new Message(Severity::Recoverable, 'out_of_stock', 'Only 2 left.', $path);

        // Assert
        self::assertSame(Severity::Recoverable, $message->severity);
        self::assertSame('out_of_stock', $message->code);
        self::assertSame('$.line_items[0]', $message->path);
    }

    #[Test]
    public function it_rejects_an_empty_code(): void
    {
        // Arrange
        $code = '';

        // Assert
        $this->expectException(InvalidCart::class);

        // Act
        new Message(Severity::Info, $code, 'Text');
    }
}
