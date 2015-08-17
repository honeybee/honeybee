<?php

namespace Honeybee\Ui\Activity;

use Honeybee\Common\ScopeKeyInterface;

interface ActivityServiceInterface
{
    public function getContainers();

    public function getContainer($scope);

    public function getActivityMap($scope);

    /**
     * Returns an activity of the given name from the given activity container scope.
     *
     * Only activities allowed for the current user are returned.
     *
     * @param string $scope activity container scope key
     * @param string $name activity name
     *
     * @return ActivityInterface|null
     */
    public function getActivity($scope, $name);
}
