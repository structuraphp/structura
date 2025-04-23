<?php

declare(strict_types=1);

namespace Structura\Concerns;

use Closure;

trait Arr
{
    /**
     * @param array<int,string> $array
     */
    public function implodeMore(array $array, string $glue = ', ', int $max = 3): string
    {
        $count = \count($array);
        if ($count <= $max) {
            return implode($glue, $array);
        }

        return \sprintf(
            '%s%s[%d+]',
            implode($glue, \array_slice($array, 0, $max)),
            $glue,
            $count - $max,
        );
    }

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
