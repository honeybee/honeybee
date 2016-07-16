<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Author;

use Honeybee\Projection\Projection;

class Author extends Projection
{
    /**
     * @return \Trellis\EntityType\Attribute\Text\Text
     */
    public function getFirstname()
    {
        return $this->get('firstname');
    }

    /**
     * @return \Trellis\EntityType\Attribute\Text\Text
     */
    public function getLastname()
    {
        return $this->get('lastname');
    }
}
