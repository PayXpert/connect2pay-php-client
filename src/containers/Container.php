<?php

namespace PayXpert\Connect2Pay\containers;

class Container implements \JsonSerializable
{
    protected static function copyScalarProperties($properties, $src, &$dest)
    {
        if ($properties !== null && is_object($src) && is_object($dest)) {
            foreach ($properties as $property) {
                if (isset($src->{$property->getName()}) && is_scalar($src->{$property->getName()})) {
                    $dest->{"set" . ucfirst($property->getName())}($src->{$property->getName()});
                }
            }
        }
    }

    protected function limitLength($data, $length)
    {
        if ($data == null || empty($data)) {
            return $data;
        }

        if ($length != null && mb_strlen($data, 'UTF-8') > $length) {
            return mb_substr($data, 0, $length, 'UTF-8');
        } else {
            return $data;
        }
    }

    public function jsonSerialize()
    {
        return self::extract_props($this);
    }

    /**
     * Allows the serialization of private properties
     *
     * @param object $object The object to serialize
     * @return array The properties extracted for serialization
     * @throws \ReflectionException
     */
    private static function extract_props($object, $serializeNull = false)
    {
        $public = new \stdClass();

        $reflection = new \ReflectionClass(get_class($object));

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            $value = $property->getValue($object);
            $name = $property->getName();

            if (is_array($value)) {
                $public->$name = [];

                foreach ($value as $key => $item) {
                    if (is_object($item)) {
                        $itemArray = self::extract_props($item);
                        $public->$name[$key] = $itemArray;
                    } else {
                        $public->$name[$key] = $item;
                    }
                }
            } else if (is_object($value)) {
                $public->$name = self::extract_props($value);
            } else if ($value !== null || $serializeNull) {
                $public->$name = $value;
            }
        }

        return $public;
    }
}