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
        /*
        $this->expression_language->register(
            'is_even_hour',
            function($str) {
                return sprintf('date("H")%2==0');
            },
            function ($arguments) {
                return date("H")%2==0;
            }
        );
        */
    }
}
