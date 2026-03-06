<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedInterfacesFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTraitsFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPreparedSets(psr12: true, laravel: true)
    ->withPhpCsFixerSets(psr12: true)
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(ConcatSpaceFixer::class, ['spacing' => 'none'])
    ->withConfiguredRule(LineLengthSniff::class, ['absoluteLineLimit' => 120])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, [
        'elements' => ['arrays', 'parameters', 'arguments'],
    ])
    ->withRules([
        ArrayIndentationFixer::class,
        MethodChainingIndentationFixer::class,
        NoSuperfluousElseifFixer::class,
        NoUnusedImportsFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        OrderedInterfacesFixer::class,
        OrderedTraitsFixer::class,
    ])
    ->withSkip([
        LineLengthSniff::class => [
            __DIR__.'/tests',
            __DIR__.'/lang',
        ],
        NewWithParenthesesFixer::class,
    ])
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/tests',
        __DIR__.'/routes',
        __DIR__.'/database',
    ]);
