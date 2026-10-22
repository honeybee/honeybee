<?php
// rector.php
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpVersion(PhpVersion::PHP_74)
    ->withRules([
        // 7.3
        \Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector::class,
        \Rector\Php73\Rector\FuncCall\RegexDashEscapeRector::class,
        \Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector::class,
        \Rector\Php73\Rector\String_\SensitiveHereNowDocRector::class,
        // 7.4
        \Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector::class,
        \Rector\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector::class,
        \Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector::class,
        \Rector\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector::class,
        \Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector::class,
        \Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector::class,
    ]);
