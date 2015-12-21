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

    /**
     * Returns the activity's URI with the given parameters/options applied. Takes the type of the Url
     * of the activity into account.
     *
     * @param ActivityInterface $activity activity (with a URL and parameters)
     * @param array $parameters parameters to merge/replace into the default URL parameters of the activity
     * @param array $options options to take into account when generating the URL
     *
     * @return string resulting URI
     */
    public function getUri(ActivityInterface $activity, array $parameters = [], array $options = []);
}
