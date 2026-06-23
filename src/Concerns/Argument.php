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

namespace Drewlabs\Overloadable\Concerns;

use Drewlabs\Overloadable\ArgumentType;

/**
 * @property string $state
 * @property string $type
 */
trait Argument
{
    /**
     * Returns true if the argument is an optional argument
     * 
     * @return bool 
     */
    public function isOptional(): bool
    {
        return ArgumentType::OPTIONAL === $this->state;
    }

    /**
     * Returns the argument type binded to the current Function argument.
     *
     * @return string|mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
