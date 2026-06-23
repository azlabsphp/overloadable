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

use Traversable;

final class TypesUtil
{

    /**
     * Zip lists into a single output list
     * 
     * @param array $first 
     * @param mixed $args 
     * @return array<array-key, array> 
     */
    public static function zip(array $first, ...$args)
    {
        $params = array_merge([static function () {
            return \func_get_args();
        }, $first], $args);
        return array_map(...$params);
    }

    /**
     * Project each value of the traversable through the callback function and return a new traversable
     * 
     * @param Traversable $iterator 
     * @param callable $callback 
     * @param bool $preserve 
     * @return \Traversable
     */
    public static function map(
        iterable $iterator,
        callable $callback,
        $preserve = true
    ) {
        return (static function () use ($iterator, $preserve, &$callback) {
            foreach ($iterator as $key => $value) {
                if ($preserve) {
                    yield $key => \call_user_func($callback, $value, $key);
                    continue;
                }
                yield \call_user_func($callback, $value, $key);
            }
        })();
    }

    /**
     * Produce a traversable instance based on the $predicate
     * 
     * @param Traversable $iterator 
     * @param callable $predicate 
     * @param bool $preserve 
     * @param int $flags 
     * @return \Traversable 
     */
    public static function filter(
        iterable $iterator,
        callable $predicate,
        $preserve = true,
        $flags = \ARRAY_FILTER_USE_BOTH
    ) {
        return (static function () use (
            $iterator,
            $preserve,
            &$predicate,
            $flags
        ) {
            foreach ($iterator as $key => $value) {
                if (!(\ARRAY_FILTER_USE_BOTH === $flags ?
                    \call_user_func($predicate, $value, $key) :
                    \call_user_func($predicate, $key))) {
                    continue;
                }
                if ($preserve) {
                    yield $key => $value;
                    continue;
                }
                yield $value;
            }
        })();
    }

    /**
     * Apply a reducer function on a iterable
     * 
     * @param \Traversable $iterator 
     * @param callable $reducer 
     * @param mixed $initial 
     * @return mixed 
     */
    public static function reduce(
        iterable $iterator,
        callable $reducer,
        $initial = null
    ) {
        $out = $initial;
        iterator_apply(
            $iterator,
            static function (\Iterator $iterator) use ($reducer, &$out) {
                [$current, $key] = [$iterator->current(), $iterator->key()];
                $out = \call_user_func($reducer, $out, $current, $key);
                return true;
            },
            [$iterator]
        );
        return $out;
    }
}
