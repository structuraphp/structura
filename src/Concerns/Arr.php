<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns;

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
}
