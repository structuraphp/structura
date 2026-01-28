<?php

declare(strict_types=1);

if (function_exists('str_replace_first')) {
    function str_replace_first(
        string $search,
        string $replace,
        string $subject,
    ): string {
        if (($pos = strpos($subject, $search)) !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
