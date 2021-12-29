<?php

declare(strict_types=1);

namespace JMS\Serializer\Handler;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\Serializer\XmlSerializationVisitor;

class BackedEnumHandler implements SubscribingHandlerInterface
{
    public function __construct(bool $xmlCData = true)
    {
        $this->xmlCData = $xmlCData;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];

        foreach ($formats as $format) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'type' => \BackedEnum::class,
                'format' => $format,
                'method' => 'serializeBackedEnum',
            ];

            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'type' => \BackedEnum::class,
                'format' => $format,
                'method' => 'deserializeBackedEnumFrom' . ucfirst($format),
            ];
        }

        return $methods;
    }

    /**
     * @return \DOMCdataSection|\DOMText|mixed
     */
    public function serializeBackedEnum(
        SerializationVisitorInterface $visitor,
        \BackedEnum $enum,
        array $type,
        SerializationContext $context
    ) {
        $reflectionEnum = new \ReflectionEnum($enum);
        if ('string' === $reflectionEnum->getBackingType()->getName()) {
            if ($visitor instanceof XmlSerializationVisitor && false === $this->xmlCData) {
                return $visitor->visitSimpleString($enum->value, $type);
            }

            return $visitor->visitString($enum->value, $type);
        }

        return $visitor->visitInteger($enum->value, $type);
    }

    public function deserializeBackedEnumFromJson(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type
    ): ?\BackedEnum {
        if (null === $data) {
            return null;
        }

        return $this->deserializeBackedEnum((string) $data, $type['name']);
    }

    public function deserializeBackedEnumYml(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type
    ): ?\BackedEnum {
        if (null === $data) {
            return null;
        }

        return $this->deserializeBackedEnum((string) $data, $type['name']);
    }

    public function deserializeBackedEnumFromXml(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type
    ): ?\BackedEnum {
        if ($this->isDataXmlNull($data)) {
            return null;
        }

        return $this->deserializeBackedEnum((string) $data, $type['name']);
    }

    public function deserializeBackedEnum(string $data, string $enumClass): \BackedEnum
    {
        $enumReflection = new \ReflectionEnum($enumClass);
        $backedType = $enumReflection->getBackingType();
        if ('int' === $backedType->getName()) {
            $data = (int) $data;
        }

        try {
            $enum = $enumClass::from($data);
        } catch (\ValueError $e) {
            throw new \RuntimeException(sprintf('Could not deserialize %s backed enum with value %s', $enumClass, $data));
        }

        return $enum;
    }

    /**
     * @param mixed $data
     */
    private function isDataXmlNull($data): bool
    {
        $attributes = $data->attributes('xsi', true);

        return isset($attributes['nil'][0]) && 'true' === (string) $attributes['nil'][0];
    }
}
