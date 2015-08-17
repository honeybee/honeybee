<?php

namespace Honeybee\Tests\Projection\Fixtures\Book;

use Honeybee\Projection\Resource\Resource;

class Paragraph extends Aggregate
{
    public function getHeadline()
    {
        return $this->getValue('headline');
    }

    public function setHeadline($headline)
    {
        return $this->setValue('headline', $headline);
    }

    public function getContent()
    {
        return $this->getValue('content');
    }

    public function setContent($content)
    {
        return $this->setValue('content', $content);
    }
}
