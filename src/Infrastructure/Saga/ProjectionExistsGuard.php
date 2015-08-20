<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use JmesPath\AstRuntime;
use Workflux\Guard\ConfigurableGuard;
use Workflux\StatefulSubjectInterface;

class ProjectionExistsGuard extends ConfigurableGuard
{
    protected $data_access_service;

    public function __construct(array $options = [], DataAccessServiceInterface $data_access_service)
    {
        parent::__construct($options);

        $this->data_access_service = $data_access_service;
    }

    public function accept(StatefulSubjectInterface $subject)
    {
        $execution_context = $subject->getExecutionContext();
        $query_service_key = $this->options->get('query_service');
        $identifier_path = $this->options->get('identifier_path');

        $payload = $subject->getPayload();
        $jmes_path_runtime = new AstRuntime();
        $identifier = $jmes_path_runtime($identifier_path, $payload);

        $query_service = $this->data_access_service->getQueryService($query_service_key);

        return $identifier ? $query_service->findByIdentifier($identifier)->getTotalCount() === 1 : false;
    }

    public function __toString()
    {
        return static::CLASS;
    }
}
