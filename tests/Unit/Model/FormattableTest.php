<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Tests\Unit\Model;

use OpenEuropa\OpenProjectClient\Exception\UnexpectedResponseException;
use OpenEuropa\OpenProjectClient\Http\HalResource;
use OpenEuropa\OpenProjectClient\Model\Formattable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Formattable::class)]
#[UsesClass(HalResource::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class FormattableTest extends TestCase
{
    public function testMapsAllFields(): void
    {
        $formattable = Formattable::fromApiResponse([
            'format' => 'markdown',
            'raw' => '**Hello**',
            'html' => '<strong>Hello</strong>',
        ]);

        self::assertSame('markdown', $formattable->format);
        self::assertSame('**Hello**', $formattable->raw);
        self::assertSame('<strong>Hello</strong>', $formattable->html);
    }

    public function testRawIsNullWhenAbsent(): void
    {
        $formattable = Formattable::fromApiResponse(['format' => 'plain']);

        self::assertSame('plain', $formattable->format);
        self::assertNull($formattable->raw);
        self::assertNull($formattable->html);
    }

    public function testRawIsNullWhenNotAString(): void
    {
        $formattable = Formattable::fromApiResponse(['format' => 'plain', 'raw' => 42]);

        self::assertNull($formattable->raw);
    }

    public function testHtmlIsNullWhenNotAString(): void
    {
        $formattable = Formattable::fromApiResponse(['format' => 'plain', 'html' => true]);

        self::assertNull($formattable->html);
    }

    public function testThrowsWhenFormatIsMissing(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('format');

        Formattable::fromApiResponse(['raw' => 'some text']);
    }

    public function testThrowsWhenFormatIsNotAString(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('format');

        Formattable::fromApiResponse(['format' => 123]);
    }
}
