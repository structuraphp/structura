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
        '@PhpCsFixer' => true,
        // Added rules
        'declare_strict_types' => true,
        'phpdoc_line_span' => ['property' => 'single', 'const' => 'single',],

        // Override @PhpCsFixer
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => [
            'import_constants' => false,
            'import_functions' => false
        ],
        'heredoc_to_nowdoc' => false,
        'increment_style' => ['style' => 'post'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha'
        ],
        'php_unit_internal_class' => false,
        'phpdoc_to_comment' => ['ignored_tags' => ['var']],
        'phpdoc_align' => ['align' => 'left'],
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arguments',
                'array_destructuring',
                'arrays',
                'match',
                'parameters',
            ]
        ],
        'yoda_style' => false,
    ])
    ->setParallelConfig(new ParallelConfig(6, 20))
    ->setRiskyAllowed(true)
    ->setFinder($finder);
