<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return PhpCsFixer\Config::create()->setRiskyAllowed(true)->setRules([
    '@PSR2'                      => true,
    'native_function_invocation' => true,
    'is_null'                    => true,
    'ordered_imports'            => true,
])->setFinder($finder);