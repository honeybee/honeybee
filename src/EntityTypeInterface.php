<?php

namespace Honeybee;

interface EntityTypeInterface
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
     * @return AttributeMap
     */
    public function getMandatoryAttributes();
}
