<?php

declare(strict_types=1);

namespace JMS\Serializer\Tests\Handler;

use JMS\Serializer\Handler\BackedEnumHandler;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Tests\Fixtures\BackedIntEnum;
use JMS\Serializer\Tests\Fixtures\BackedStringEnum;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use PHPUnit\Framework\TestCase;

class BackedEnumHandlerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testSerializeIntBackedEnum()
    {
        $handler = new BackedEnumHandler();

        $visitor = $this->getMockBuilder(SerializationVisitorInterface::class)->getMock();
        $visitor->method('visitInteger')->with(1)->willReturn(1);

        $context = $this->getMockBuilder(SerializationContext::class)->getMock();
        $type = ['name' => \BackedEnum::class, 'params' => []];

        $handler->serializeBackedEnum($visitor, BackedIntEnum::FOO, $type, $context);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSerializeStringBackedEnum()
    {
        $handler = new BackedEnumHandler();

        $visitor = $this->getMockBuilder(SerializationVisitorInterface::class)->getMock();
        $visitor->method('visitString')->with('foo')->willReturn('foo');

        $context = $this->getMockBuilder(SerializationContext::class)->getMock();
        $type = ['name' => \BackedEnum::class, 'params' => []];

        $handler->serializeBackedEnum($visitor, BackedStringEnum::FOO, $type, $context);
    }

    public function testDeserializeIntBackedEnum()
    {
        $handler = new BackedEnumHandler();

        self::assertEquals(BackedIntEnum::FOO, $handler->deserializeBackedEnum('1', BackedIntEnum::class));
    }

    public function testDeserializeStringBackedEnum()
    {
        $handler = new BackedEnumHandler();

        self::assertEquals(BackedStringEnum::FOO, $handler->deserializeBackedEnum('foo', BackedStringEnum::class));
    }
}
