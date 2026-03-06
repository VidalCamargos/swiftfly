<?php declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\CodeQuality\Rector\ClassMethod\NoSetupWithParentCallOverrideRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Rector\FuncCall\ArgumentFuncCallToMethodCallRector;
use RectorLaravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withParallel(600, 4)
    ->withImportNames(importShortClasses:  false)
    ->withCache(__DIR__.'/.rector', FileCacheStorage::class)
    ->withSets([
        LaravelSetList::LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_STATIC_TO_INJECTION,
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withRules([
        DataProviderAnnotationToAttributeRector::class,
        RemoveAlwaysElseRector::class,
        SimplifyUselessVariableRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withSkip([
        ArgumentAdderRector::class,
        ArgumentFuncCallToMethodCallRector::class,
        CarbonToDateFacadeRector::class,
        CompleteDynamicPropertiesRector::class,
        HelperFuncCallToFacadeClassRector::class,
        StaticCallToMethodCallRector::class,
        AssertEmptyNullableObjectToAssertInstanceofRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        NoSetupWithParentCallOverrideRector::class,
        DeclareStrictTypesTestsRector::class,
        FinalizeTestCaseClassRector::class,
    ])
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/lang',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ]);
