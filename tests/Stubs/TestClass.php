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

use Drewlabs\Overloadable\Overloadable;

class TestClass
{
    use Overloadable;

    /**
     * @param mixed ...$args 
     * @return mixed 
     */
    public function log(...$args)
    {
        return $this->overload($args, [
            static function (ConsoleLogger $logger) {
                return $logger->log();
            },
            static function (FileLogger $logger, ?string $prefix = null) {
                return $logger->log($prefix ?? 'ERROR024');
            },
        ]);
    }

    /**
     * @param mixed ...$args 
     * @return mixed 
     */
    public function writeLog(...$args)
    {
        return $this->overload($args, [
            static function (Console&Logger $logger, string $value) {
                return $logger->write($value);
            },
            static function (Logger|Console $logger, ?string $prefix = null) {
                return $logger->log($prefix ?? 'ERROR024');
            },
        ]);
    }
}
