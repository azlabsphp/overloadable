<?php

namespace Drewlabs\Overloadable;

use ReflectionIntersectionType;

class IntersectionTypeArgument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ReflectionIntersectionType
     */
    private $type;

    /**
     * @var bool
     */
    private $optional;

    public function __construct(ReflectionIntersectionType $type, string $name = null, bool $optional = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->optional = $optional;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * returns the argument type binded to the current Function argument.
     *
     * @return string
     */
    public function getType()
    {
        $names = [];
        /**
         * @var \ReflectionNamedType|\ReflectionIntersectionType|\ReflectionUnionType
         */
        foreach ($this->type->getTypes() as $type) {
            $names[] = $type->getName();
        }
        return implode('&', $names);
    }


    /**
     * Checks if `$value` exists in `getType()` returned string
     *  
     * @param mixed $value 
     * 
     * @return bool 
     */
    public function match($value)
    {
        if (is_null($value) && $this->isOptional()) {
            return true;
        }

        /**
         * @var \ReflectionNamedType|\ReflectionIntersectionType|\ReflectionUnionType
         */
        foreach ($this->type->getTypes() as $type) {
            $factories = [
                \ReflectionNamedType::class => function ($v) use ($type) {
                    return (new Argument($type->getName(), $type->allowsNull()))->match($v);
                },
                \ReflectionIntersectionType::class => function ($v) use ($type) {
                    return (new static($type, null, $type->allowsNull()))->match($v);
                },
                \ReflectionUnionType::class => function ($v) use ($type) {
                    return (new UnionTypeArgument($type, null, $type->allowsNull()))->match($v);
                },
            ];
            if (($callback = $factories[get_class($type)]) && (false === call_user_func_array($callback, [$value]))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle type conversion to string.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s:%s', $this->getType(), $this->isOptional() ? ArgumentType::OPTIONAL : ArgumentType::REQUIRED);
    }
}
