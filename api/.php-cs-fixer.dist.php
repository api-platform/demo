<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'php_unit_data_provider_name' => false,
        'php_unit_data_provider_return_type' => true,
        'php_unit_data_provider_static' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'php_unit_expectation' => true,
        'php_unit_fqcn_annotation' => true,
        'php_unit_internal_class' => false,
        'php_unit_method_casing' => true,
        'php_unit_mock_short_will_return' => true,
        'php_unit_namespaced' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => false,
        'php_unit_test_annotation' => ['style' => 'annotation'],
        'php_unit_test_case_static_method_calls' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_to_comment' => false,
        'void_return' => true,
        'concat_space' => ['spacing' => 'one'],
        'braces' => ['allow_single_line_closure' => true],
        'nullable_type_declaration' => true,
    ])
    ->setFinder($finder)
;
