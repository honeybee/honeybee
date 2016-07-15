<?php

namespace Honeybee;

use Trellis\EntityType\EntityTypeInterface as TrellisEntityTypeInterface;

interface EntityTypeInterface extends TrellisEntityTypeInterface
{
    public function isHierarchical();

    /**
     * Returns the attributes of the current entity type (and its embedded entities)
     * that are capable of handling file properties (a location, mimetype, extension).
     *
     * @see HandlesFileListInterface
     * @see HandlesFileInterface
     *
     * @return array with attribute_path => attribute
     */
    public function getFileHandlingAttributes();

    /**
     * @return \Trellis\\EntityType\Attribute\AttributeMap
     */
    public function getMandatoryAttributes();
}
