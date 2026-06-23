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

namespace Drewlabs\Overloadable\Tests\Stubs;

use Drewlabs\Overloadable\ArgumentType;
use Drewlabs\Overloadable\Overloadable;

class MethodOverloadClass
{
    use Overloadable;

    /**
     * @param mixed ...$args 
     * @return mixed 
     */
    public function someMethod(...$args)
    {
        return $this->overload($args, [
            // Call this closure if two args are passed and the first is an int
            static function (int $a, $b) {
                return 'From the Closure';
            },

            // Call this method if the args match the args of `methodA` (uses reflection)
            'methodA',

            // Call this method if the args match the args of `methodB` (uses reflection)
            'methodB',

            // Call methodC if exactly 2 arguments of any type are passed
            'methodC' => ['*', '*'],

            // // Call methodD if 3 args are passed and the first is an array
            'methodD' => ['array', \string::class, ['*', ArgumentType::OPTIONAL]],

            // // Call methodE if 3 args are passed and the last is a closure
            'methodE' => ['*', '*', \Closure::class],
        ]);
    }

    private function methodA()
    {
        return sprintf('METHOD %s', 'A');
    }

    private function methodB(\DateTime $arg1, array $arg2, int $arg3)
    {
        return sprintf('METHOD %s', 'B');
    }

    private function methodC($arg1, $arg2)
    {
        return sprintf('METHOD %s', 'C');
    }

    private function methodD($arg1, $arg2, $arg3 = null)
    {
        return sprintf('METHOD %s', 'D');
    }

    private function methodE($arg1, $arg2, $arg3)
    {
        return sprintf('METHOD %s', 'E');
    }
}
