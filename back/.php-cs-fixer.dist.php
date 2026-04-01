<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/tests',
        __DIR__.'/bin',
    ])
    ->exclude('var')
    ->exclude('vendor')
    ->notPath('Kernel.php')
    ->name('*.php')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP8x2Migration' => true,
        '@PHPUnit10x0Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal_by_scope',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try', 'yield', 'yield_from'],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,
        'single_import_per_statement' => true,
        'concat_space' => ['spacing' => 'one'],
        'method_argument_space' => [
            'attribute_placement' => 'standalone',
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'new_with_parentheses' => ['anonymous_class' => false],
        'no_extra_blank_lines' => [
            'tokens' => ['attribute', 'break', 'case', 'continue', 'curly_brace_block', 'extra', 'parenthesis_brace_block', 'return', 'throw', 'use'],
        ],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => ['order' => ['param', 'return', 'throws']],
        'phpdoc_separation' => true,
        'single_line_empty_body' => true,
        'single_quote' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
        ],
        'modifier_keywords' => ['elements' => ['const', 'method', 'property']],
        'declare_strict_types' => false,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'strict' => true,
        ],
    ])
    ->setLineEnding("\n")
    ->setIndent('    ')
    ->setUsingCache(true)
    ->setFinder($finder)
;
