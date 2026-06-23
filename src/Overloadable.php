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

use Drewlabs\Overloadable\DataTypes;

trait Overloadable
{
    /**
     * Provide a method overload implementations to PHP classes.
     *
     * @param array $args
     * @param array $signatures
     *
     * @return mixed
     */
    public function overload($args, $signatures)
    {
        $fallbacks = [];
        $handlers = TypesUtil::filter(
            TypesUtil::map(
                new \ArrayIterator($signatures ?? []),
                function ($value, $key) {
                    return new OverloadedMethod($value, $key, $this);
                }
            ),
            static function (OverloadedMethod $candidate) use ($args, $fallbacks) {
                if ($candidate->isFallback()) {
                    $fallbacks[] = $candidate;
                }
                return $candidate->matches($args ?? []);
            },
            false
        );
        $handlers = iterator_to_array($handlers);
        $total_handlers = \count($handlers);
        if (
            1 === $total_handlers &&
            (1 === \count($fallbacks)) &&
            (null !== $method = $fallbacks[0])
        ) {
            return $method->call($args);
        } elseif (1 === $total_handlers) {
            if ($method = $this->getMethod($handlers)) {
                return $method->call($args);
            }
        } else {
            // Look for the method having a more specific argument type definition
            $handler = TypesUtil::reduce(
                new \ArrayIterator($handlers),
                static function ($carry, $curr) {
                    if (null === $carry) {
                        return $curr;
                    }
                    $arguments = $curr->getArguments();
                    $carry_arguments = $carry->getArguments();
                    
                    foreach (TypesUtil::zip($arguments, $carry_arguments) as $value) {
                        if (false !== strpos($value[0] ?? '', sprintf('%s:', DataTypes::ANY))) {
                            $carry = $carry;
                            break;
                        }
                        if (false !== strpos($value[1] ?? '', sprintf('%s:', DataTypes::ANY))) {
                            $carry = $curr;
                            break;
                        }
                    }
                    return $carry;
                },
                null
            );
            if ($handler) {
                return $handler->call($args);
            }
            throw new MethodCallExpection(sprintf('%d method provide the same method definition', $total_handlers));
        }
        throw new MethodCallExpection('No suitable overloaded method found.');
    }

    /**
     * @param array $values
     * 
     * @return OverloadedMethod
     */
    private function getMethod($values)
    {
        return $values[0] ?? null;
    }
}
