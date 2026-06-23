<?php

declare(strict_types=1);

/*
 * This file is part of the Drewlabs package.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Overloadable;

use Drewlabs\Overloadable\Argument;
use Drewlabs\Overloadable\ArgumentType;
use Drewlabs\Overloadable\DataTypes;
use Drewlabs\Overloadable\NamedArgument;
use ReflectionIntersectionType;
use ReflectionUnionType;

class OverloadMethodArguments
{
    /**
     * 
     * @var array
     */
    private $all;
    /**
     * 
     * @var array
     */
    private $required;
    /**
     * 
     * @var array
     */
    private $optional;
    /**
     * @var int
     */
    private $required_count;
    /**
     * 
     * @var int
     */
    private $optional_count;

    /**
     * Create class instance
     * 
     * @param array $all 
     * @param array $required 
     * @param array $optional 
     * @param int $required_count 
     * @param int $optional_count 
     * @return void 
     */
    public function __construct(
        array $all,
        array $required,
        array $optional,
        int $required_count,
        int $optional_count
    ) {
        $this->all = $all;
        $this->required = $required;
        $this->optional = $optional;
        $this->required_count = $required_count;
        $this->optional_count = $optional_count;
    }

    /**
     * Creates instance from array signature
     * 
     * @param array $values 
     * @return OverloadMethodArguments 
     */
    public static function fromArray(array $values = [])
    {
        $optinal_args_count = 0;
        $required_args_count = 0;
        $required_arguments = [];
        $optional_arguments = [];
        // #endregion Initialize values
        foreach ($values as $curr) {
            $type = DataTypes::ANY;
            $state = ArgumentType::REQUIRED;
            if (\is_string($curr)) {
                // Argument is required
                $type = $curr;
            } elseif (\is_array($curr)) {
                $type = DataTypes::ANY;
                $total_items = \count($curr);
                if ($total_items > 0) {
                    $type = $curr[0] ?? DataTypes::ANY;
                    if (($total_items > 1 ? ($curr[1] ?? ArgumentType::OPTIONAL) : ArgumentType::REQUIRED) === ArgumentType::OPTIONAL) {
                        $state = ArgumentType::OPTIONAL;
                    }
                }
            } else {
                // Argument is of type any and is optional
                $type = DataTypes::ANY;
                $state = ArgumentType::OPTIONAL;
            }
            $funcArg = new Argument($type, $state === ArgumentType::OPTIONAL);
            if ($funcArg->isOptional()) {
                ++$optinal_args_count;
                $optional_arguments[] = $funcArg;
            }
            if (!$funcArg->isOptional()) {
                ++$required_args_count;
                $required_arguments[] = $funcArg;
            }
            $carr[] = $funcArg;
        }

        return new OverloadMethodArguments(
            self::normalizeTypes([...$required_arguments, ...$optional_arguments]),
            $required_arguments,
            $optional_arguments,
            $required_args_count,
            $optinal_args_count,
        );
    }

    /**
     * Create instance from reflection function instance
     * 
     * @param \ReflectionFunctionAbstract $reflection 
     * @return static 
     */
    public static function fromReflection($reflection)
    {
        $optinal_args_count = 0;
        $required_args_count = 0;
        $optional_arguments = [];
        $required_arguments = [];
        // #endregion Initialize values
        foreach ($reflection->getParameters() as $parameter) {
            /**
             * @var \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType
             */
            $type = $parameter->getType();
            if (class_exists(ReflectionUnionType::class) && ($type instanceof ReflectionUnionType)) {
                $arg = new UnionTypeArgument($type, $parameter->getName(), $parameter->isOptional());
            } else if (class_exists(ReflectionIntersectionType::class) && $type instanceof ReflectionIntersectionType) {
                $arg = new IntersectionTypeArgument($type, $parameter->getName(), $parameter->isOptional());
            } else if (!$parameter->hasType()) {
                $arg = new NamedArgument($parameter->getName(), DataTypes::ANY, $parameter->isOptional());
            } else {
                $arg = new NamedArgument($parameter->getName(), $type->getName(), $parameter->isOptional());
            }
            if ($parameter->isOptional()) {
                ++$optinal_args_count;
                $optional_arguments[] = $arg;
            } else {
                ++$required_args_count;
                $required_arguments[] = $arg;
            }
        }

        return new self(
            self::normalizeTypes([...$required_arguments, ...$optional_arguments]),
            $required_arguments,
            $optional_arguments,
            $required_args_count,
            $optinal_args_count,
        );
    }

    /**
     * Retruns the argument count value
     * 
     * @return int
     */
    public function count()
    {
        return \count($this->getAll() ?? []);
    }

    /**
     * Returns agument length. It's an alias to the argument
     * count method definition
     * 
     * @return int
     */
    public function length()
    {
        return $this->count();
    }

    /**
     * Returnn all argument as array
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->all ?? [];
    }

    /**
     * Returns list of required arguments
     * 
     * @return array[]
     */
    public function getRequiredArguments()
    {
        return $this->required ?? [];
    }

    /**
     * Returns list of optional arguments
     * 
     * @return array[]
     */
    public function getOptionalArguments()
    {
        return $this->optional ?? [];
    }

    /**
     * Returns required arguments count
     * 
     * @return int
     */
    public function requiredArgumentsCount()
    {
        return $this->required_count;
    }

    /**
     * Returns optional arguments count
     * 
     * @return int
     */
    public function optionalArgumentsCount()
    {
        return $this->optional_count;
    }

    /**
     * Normalize function argument types
     *
     * @param NamedArgument[]|Argument[] $types
     *
     * @return Argument[]|Argument[]
     */
    private static function normalizeTypes(array $types)
    {
        return array_map(static function ($type) {
            switch ($type->getType()) {
                case 'int':
                    return new NamedArgument(
                        $type instanceof NamedArgument ? $type->getName() : '*',
                        DataTypes::T_INTEGER,
                        $type->isOptional()
                    );
                case 'bool':
                    return new NamedArgument(
                        $type instanceof NamedArgument ? $type->getName() : '*',
                        DataTypes::T_BOOLEAN,
                        $type->isOptional()
                    );
                default:
                    return $type;
            }
        }, $types);
    }
}
