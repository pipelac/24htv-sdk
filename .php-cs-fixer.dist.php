<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'no_whitespace_in_blank_line' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'no_extra_blank_lines' => true,
        'visibility_required' => false,
    ])
    ->setFinder($finder)
    ->setUsingCache(true);
