<?php

declare(strict_types=1);

namespace Lada\Core\Tests\Unit\Domain\Shared;

use Lada\Core\Domain\Cart\CartRef;
use Lada\Core\Domain\Catalog\ProductRef;
use Lada\Core\Domain\Exception\InvalidIdentifier;
use Lada\Core\Domain\Order\OrderRef;
use Lada\Core\Domain\Shared\Reference;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReferenceTest extends TestCase
{
    /**
     * @param class-string<Reference> $class
     */
    #[Test]
    #[DataProvider('referenceClasses')]
    public function it_keeps_the_shop_value_untouched(string $class): void
    {
        // Arrange
        $value = 'cart:42/ÄÖ';

        // Act
        $reference = new $class($value);

        // Assert
        self::assertSame($value, $reference->value);
        self::assertSame($value, (string) $reference);
    }

    /**
     * @param class-string<Reference> $class
     */
    #[Test]
    #[DataProvider('referenceClasses')]
    public function it_accepts_255_characters(string $class): void
    {
        // Arrange
        $value = str_repeat('ż', 255);

        // Act
        $reference = new $class($value);

        // Assert
        self::assertSame($value, $reference->value);
    }

    /**
     * @param class-string<Reference> $class
     */
    #[Test]
    #[DataProvider('invalidValues')]
    public function it_rejects_an_invalid_value(string $class, string $value): void
    {
        // Arrange
        // (class and value from the data provider)

        // Assert
        $this->expectException(InvalidIdentifier::class);

        // Act
        new $class($value);
    }

    /**
     * @return iterable<string, array{class-string<Reference>, string}>
     */
    public static function invalidValues(): iterable
    {
        foreach (self::referenceClasses() as $name => [$class]) {
            yield $name . ': empty' => [$class, ''];
            yield $name . ': leading whitespace' => [$class, ' 42'];
            yield $name . ': trailing whitespace' => [$class, "42\n"];
            yield $name . ': too long' => [$class, str_repeat('a', 256)];
        }
    }

    #[Test]
    public function it_equals_a_reference_of_the_same_type_and_value(): void
    {
        // Arrange
        $a = new CartRef('42');
        $b = new CartRef('42');

        // Act
        $equals = $a->equals($b);

        // Assert
        self::assertTrue($equals);
    }

    #[Test]
    public function it_does_not_equal_a_reference_with_another_value(): void
    {
        // Arrange
        $a = new CartRef('42');
        $b = new CartRef('43');

        // Act
        $equals = $a->equals($b);

        // Assert
        self::assertFalse($equals);
    }

    #[Test]
    public function it_does_not_equal_a_reference_of_another_type(): void
    {
        // Arrange
        $cart = new CartRef('42');
        $order = new OrderRef('42');

        // Act
        $equals = $cart->equals($order);

        // Assert
        self::assertFalse($equals);
    }

    /**
     * @return iterable<string, array{class-string<Reference>}>
     */
    public static function referenceClasses(): iterable
    {
        yield 'cart' => [CartRef::class];
        yield 'order' => [OrderRef::class];
        yield 'product' => [ProductRef::class];
    }
}
