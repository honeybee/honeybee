<?php

namespace Honeybee\Ui\Renderer\Text\Honeybee\Ui\Activity;

use Honeybee\Ui\Renderer\ActivityRenderer;

class TextActivityRenderer extends ActivityRenderer
{
    public function doRender()
    {
        return $this->getLinkfor(
            $this->getPayload('subject')
        );
    }
}
