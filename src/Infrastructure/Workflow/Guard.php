<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\EnvironmentInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Workflux\Guard\GuardInterface;
use Workflux\StatefulSubjectInterface;

/**
 * Determines whether a state transition is acceptable by evaulating an expression.
 */
class Guard implements GuardInterface
{
    /**
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * @var ExpressionServiceInterface $expression_service
     */
    protected $expression_service;

    /**
     * @var Settings
     */
    protected $options;

    /**
     * Creates a new Guard instance.
     *
     * @param EnvironmentInterface $environment
     * @param ExpressionServiceInterface $expression_service
     * @param array $options with at least an 'expression' string
     */
    public function __construct(
        EnvironmentInterface $environment,
        ExpressionServiceInterface $expression_service,
        array $options
    ) {
        if (!isset($options['expression']) || empty($options['expression'])) {
            throw new RuntimeError('Given options must contain a non-empty "expression" string.');
        }
        $this->environment = $environment;
        $this->expression_service = $expression_service;
        $this->options = new Settings($options);
    }

    /**
     * Evaluates the configured expression for the given subject. The expression may
     * use the current user as "current_user" and the subject as "subject".
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return boolean true if transition is acceptable
     */
    public function accept(StatefulSubjectInterface $subject)
    {
        $execution_context = $subject->getExecutionContext();
        $parameters = $execution_context->getParameters();

        if (is_array($parameters)) {
            $params = $parameters;
        } elseif (is_object($parameters) && is_callable(array($parameters, 'toArray'))) {
            $params = $parameters->toArray();
        } else {
            throw new RuntimeError(
                'The $subject->getExecutionContext()->getParameters() must return array or object with toArray method.'
            );
        }

        $transition_acceptable = $this->expression_service->evaluate(
            $this->options->get('expression'),
            array_merge(
                [
                    'subject' => $subject,
                    'current_user' => $this->environment->getUser(),
                ],
                $params
            )
        );

        return (bool)$transition_acceptable;
    }

    /**
     * Returns a string represenation of the guard.
     *
     * @return string
     */
    public function __toString()
    {
        return ' if ' . $this->options->get('expression');
    }
}
