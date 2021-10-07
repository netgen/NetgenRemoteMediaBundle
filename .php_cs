<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,

        // Overrides for rules included in PhpCsFixer rule sets
        'array_syntax' => ['syntax' => 'short'],
        'cast_spaces' => ['space' => 'single'],
        'concat_space' => ['spacing' => 'one'],
        'method_chaining_indentation' => false,
        'multiline_whitespace_before_semicolons' => false,
        'native_function_invocation' => ['include' => ['@all']],
        'no_superfluous_phpdoc_tags' => false,
        'no_alias_functions' => true,
        'no_unset_on_property' => false,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'php_unit_internal_class' => false,
        'php_unit_method_casing' => false,
        'php_unit_strict' => false,
        'php_unit_test_annotation' => false,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_align' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'return_assignment' => false,
        'self_accessor' => false,
        'single_line_comment_style' => false,
        'space_after_semicolon' => false,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],

        // Additional rules
        'declare_strict_types' => true,
        'date_time_immutable' => true,
        'global_namespace_import' => [
            'import_classes' => null,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'heredoc_indentation' => ['indentation' => 'same_as_start'],
        'list_syntax' => ['syntax' => 'short'],
        'mb_str_functions' => true,
        'native_constant_invocation' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'static_lambda' => true,
        'ternary_to_null_coalescing' => true,
        'use_arrow_functions' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['vendor', 'docs', 'ezpublish_legacy'])
            ->in(__DIR__)
    )
;
