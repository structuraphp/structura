<?php

declare(strict_types=1);

namespace Structura\Concerns;

use Closure;

trait Arr
{
    /**
     * @param array<int,string> $array
     */
    public function first(array $array, Closure $closure): bool
    {
        foreach ($array as $key => $value) {
            if ($closure($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
