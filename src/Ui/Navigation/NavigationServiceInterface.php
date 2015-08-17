<?php

namespace Honeybee\Ui\Navigation;

interface NavigationServiceInterface
{
    /**
     * Returns the navigation known for the specified name. Returns the
     * default navigation when no name is specified.
     *
     * @param string $navigation_name name of the navigation to return
     *
     * @return NavigationInterface wanted navigation (or the default navigation)
     */
    public function getNavigation($navigation_name = null);

    /**
     * @return string name of the default navigation
     */
    public function getDefaultNavigationName();
}
