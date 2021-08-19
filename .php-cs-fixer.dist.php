<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath('var');

$config = new PhpCsFixer\Config();
$config->setRiskyAllowed(true)->setRules([
    '@Symfony' => true,
    'blank_line_after_opening_tag' => true,
    'is_null' => true,
    'modernize_types_casting' => true,
    'self_accessor' => true,
    'dir_constant' => true,
    'ordered_class_elements' => true,
    'declare_strict_types' => true,
    'no_superfluous_elseif' => true,
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
])->setFinder($finder);

return $config;