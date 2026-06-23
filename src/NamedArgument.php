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

class NamedArgument
{
    /**
     * @var bool
     */
    private $optional;

    /**
     * Property holding the parameter type.
     *
     * @var string|mixed
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * Creates class instance
     * 
     * @param string        $name 
     * @param null|string   $type 
     * @param bool          $optional
     * 
     * @return void 
     */
    public function __construct(
        string $name = 'unknown',
        string $type = DataTypes::ANY,
        bool $optional = false
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->optional = $optional;
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
        return $this->type;
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

        if (DataTypes::ANY === $this->type) {
            return true;
        }

        if (is_object($value) && is_a($value, $this->type)) {
            return true;
        }

        return gettype($value) === $this->type;
    }
}
