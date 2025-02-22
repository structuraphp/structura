<?php

declare(strict_types=1);

/**
 * @see https://mlocati.github.io/php-cs-fixer-configurator
 */

use PhpCsFixer\Runner\Parallel\ParallelConfig;

$finder = PhpCsFixer\Finder::create()
    ->exclude('build')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config
    ->setCacheFile(__DIR__ . '/build/phpCsFixer/.php-cs-fixer.cache')
    ->setRules([
        '@PER-CS2.0' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'cast_spaces' => ['space' => 'single'],
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_constants' => false,
            'import_functions' => false,
        ],
        'heredoc_to_nowdoc' => false,
        'increment_style' => ['style' => 'post'],
        'native_function_invocation' => ['strict' => false],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'no_trailing_comma_in_singleline' => true,
        'no_unreachable_default_argument_value' => false,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_line_span' => [
            'property' => 'single',
            'const' => 'single',
        ],
        'phpdoc_summary' => false,
        'single_line_throw' => false,
        'yoda_style' => false,
    ])
    ->setParallelConfig(new ParallelConfig(6, 20))
    ->setRiskyAllowed(true)
    ->setFinder($finder);
