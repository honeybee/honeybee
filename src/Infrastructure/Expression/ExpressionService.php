<?php

namespace Honeybee\Infrastructure\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionService implements ExpressionServiceInterface
{
    protected $expression_language;

    public function __construct(ExpressionLanguage $expression_language)
    {
        $this->expression_language = $expression_language;
        $this->registerExtensions($expression_language);
    }

    public function evaluate($expression, array $expression_vars = array())
    {
        return $this->expression_language->evaluate($expression, $expression_vars);
    }

    protected function registerExtensions(ExpressionLanguage $expression_language)
    {
        // @see http://symfony.com/doc/current/components/expression_language/syntax.html
        $this->expression_language->register(
            'match_event',
            function($event, $pattern) {
                return sprintf('(!!preg_match("%1$s", "%2$s"))', $pattern, $event->getType());
            },
            function ($args, $event, $pattern) {
                return !!preg_match("~$pattern~", $event->getType());
            }
        );
    }
}
