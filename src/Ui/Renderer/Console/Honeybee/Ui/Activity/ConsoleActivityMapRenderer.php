<?php

namespace Honeybee\Ui\Renderer\Console\Honeybee\Ui\Activity;

use Honeybee\Ui\Renderer\Text\Honeybee\Ui\Activity\TextActivityMapRenderer;

class ConsoleActivityMapRenderer extends TextActivityMapRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return 'console/activity_map/as_list.twig';
    }
}
