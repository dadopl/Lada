<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__ . '/packages')
    ->append([__FILE__]);

return new PhpCsFixer\Config()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PHP8x4Migration' => true,
        '@PHP8x4Migration:risky' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'php_unit_test_annotation' => ['style' => 'prefix'],
        'strict_comparison' => true,
        'strict_param' => true,
        'void_return' => true,
    ])
    ->setFinder($finder);
