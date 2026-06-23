<?php

namespace Drewlabs\Overloadable;

use ReflectionUnionType;

final class UnionTypeArgument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ReflectionUnionType
     */
    private $type;

    /**
     * @var bool
     */
    private $optional;

    /**
     * Class constructor
     * 
     * @param ReflectionUnionType $type 
     * @param string|null $name 
     * @param bool $optional 
     */
    public function __construct(ReflectionUnionType $type, ?string $name = null, bool $optional = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->optional = $optional;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * Returns the argument type binded to the current Function argument.
     *
     * @return string
     */
    public function getType()
    {
        $names = [];
        /**
         * @var \ReflectionNamedType|\ReflectionIntersectionType
         */
        foreach ($this->type->getTypes() as $type) {
            $names[] = $type instanceof \ReflectionNamedType ? $type->getName() : sprintf("(%s)", (new IntersectionTypeArgument($type))->getType());
        }
        return implode('|', $names);
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
         * @var \ReflectionNamedType|\ReflectionIntersectionType
         */
        foreach ($this->type->getTypes() as $type) {
            $factories = [
                \ReflectionNamedType::class => function ($v) use ($type) {
                    return (new Argument($type->getName(), $type->allowsNull()))->match($v);
                },
                \ReflectionIntersectionType::class => function ($v) use ($type) {
                    return (new IntersectionTypeArgument($type, null, $type->allowsNull()))->match($v);
                },
            ];
            foreach (is_array($type) ? $type : [$type] as $t) {
                if (($callback = $factories[get_class($t)]) && (call_user_func_array($callback, [$value]))) {
                    return true;
                }
            }
        }
        return false;
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
