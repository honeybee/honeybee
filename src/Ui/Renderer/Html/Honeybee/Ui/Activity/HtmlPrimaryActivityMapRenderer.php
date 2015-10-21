<?php

namespace Honeybee\Ui\Renderer\Html\Honeybee\Ui\Activity;

use Honeybee\Ui\Renderer\Html\Honeybee\Ui\Activity\HtmlActivityMapRenderer;

class HtmlPrimaryActivityMapRenderer extends HtmlActivityMapRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/activity_map/primary_activities.twig';
    }
}
