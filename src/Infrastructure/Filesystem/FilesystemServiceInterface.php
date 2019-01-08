<?php

namespace Honeybee\Infrastructure\Filesystem;

use Trellis\Runtime\Attribute\AttributeInterface;
use Honeybee\EntityTypeInterface;

/**
 * Please note: a prefix is the scheme name plus scheme separator, e.g. 'userfiles://'.
 */
interface FilesystemServiceInterface
{
    const PATH_SEPARATOR = '/';

    const SCHEME_SEPARATOR = '://';

    const SCHEME_FILES = 'files';

    const SCHEME_TEMPFILES = 'tempfiles';

    /**
     * Copies a file from one location to the other.
     *
     * @param string $from_uri source location (including prefix) of file to copy, e.g. "tempfiles://foo.jpg"
     * @param string $to_uri target location (including prefix), e.g. "files://some/path/bar.jpg"
     *
     * @return boolean true on success
     */
    public function copy($from_uri, $to_uri);

    /**
     * List the contents of a directory.
     *
     * @param string $uri location of the folder to list
     * @param boolean $recursive whether to include all children recursively
     *
     * @return array of file information
     */
    public function listContents($uri = '', $recursive = false);

    /**
     * Check whether a file exists.
     *
     * @param string $uri location of the file (including prefix)
     *
     * @return boolean true if the uri target exists; false otherwise.
     */
    public function has($uri);

    /**
     * Get the filesize of a file or directory.
     *
     * @param string $uri location of file including prefix
     *
     * @return int|false size of file in bytes
     */
    public function getSize($uri);

    /**
     * Get the mime type of a file (usually without the encoding information).
     *
     * @param string $uri location of the file to examine (including prefix)
     *
     * @return string|false mime type
     */
    public function getMimetype($uri);

    /**
     * Get the timestamp of a file.
     *
     * @param string $uri location of the file to examine (including prefix)
     *
     * @return int|false
     */
    public function getTimestamp($uri);

    /**
     * Read an existing file as a stream.
     *
     * @param string $uri location of the file (including prefix)
     *
     * @return resource|false
     */
    public function readStream($uri);

    /**
     * Write a new file using a stream.
     *
     * @param string $uri location to write to (including prefix)
     * @param resource $resource resource to read from
     * @param array $config options
     *
     * @return boolean true on success; false otherwise
     */
    public function writeStream($uri, $resource, array $config = []);

    /**
     * Rename a file.
     *
     * @param string $from_uri source location (including prefix)
     * @param string $to_uri target location (including prefix)
     *
     * @return boolean true on successful renaming
     */
    public function rename($from_uri, $to_uri);

    /**
     * Delete a file.
     *
     * @param string $uri location of file to remove (including prefix)
     *
     * @return boolean true on successful deletion
     */
    public function delete($uri);

    /**
     * Delete a directory.
     *
     * @param string $uri location (directory) to delete (including prefix)
     *
     * @return boolean true on successful deletion of the folder
     */
    public function deleteDir($uri);

    /**
     * Create a directory.
     *
     * @param string $uri location (directory) to create (including prefix)
     * @param array $config options
     *
     * @return boolean true on successful creation
     */
    public function createDir($uri, array $config = []);

    //public function emptyDir

    /**
     * Creates a URI to the main asset/file storage for the given aggregate root type.
     * Defaults to the common scheme ('files') when no aggregate root type is given.
     *
     * @param string $relative_file_path identifier of the file to work with
     * @param EntityTypeInterface $entity_type type to use for filesystem scheme generation
     *
     * @return string URI for the given file, e.g. "files://relative/file/path"
     */
    public function createUri($relative_file_path, EntityTypeInterface $entity_type = null);

    /**
     * Creates a URI to the temporary asset/file storage for the given aggregate root type.
     * Defaults to the common scheme ("tempfiles") when no aggregate root type is given.
     *
     * @param string $relative_file_path identifier of the file to work with
     * @param EntityTypeInterface $entity_type type to use for filesystem scheme generation
     *
     * @return string URI for the given file, e.g. "tempfiles://relative/file/path"
     */
    public function createTempUri($relative_file_path, EntityTypeInterface $entity_type = null);

    /**
     * Returns the prefix that is used for access of the main file/asset storage for
     * the given aggregate root type. Without given type the default prefix for the
     * common file/asset storage is returned ("files://").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string scheme with separator, e.g. 'files://' or 'userfiles://'
     */
    public function getPrefix(EntityTypeInterface $entity_type = null);

    /**
     * Returns the prefix that is used for access of the temporary file/asset storage
     * for the given aggregate root type. When no type is given the default prefix
     * for the common temporary file/asset storage is returned ("tempfiles://").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string prefix, e.g. 'tempfiles://' or 'usertempfiles://'
     */
    public function getTempPrefix(EntityTypeInterface $entity_type = null);

    /**
     * Returns the scheme that is used for access of the main file/asset storage for
     * the given aggregate root type. When no type is given the default scheme name
     * for the common file/asset storage is returned ("files").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string scheme name, e.g. 'files' or 'userfiles'
     */
    public function getScheme(EntityTypeInterface $entity_type = null);

    /**
     * Returns the scheme that is used for access of the temporary file/asset storage for
     * the given aggregate root type. When no type is given the default scheme name
     * for the common temporary file/asset storage is returned ("tempfiles").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string scheme name, e.g. 'tempfiles' or 'usertempfiles'
     */
    public function getTempScheme(EntityTypeInterface $entity_type = null);

    /**
     * Generates a unique file identifier that may be used as a relative file path.
     *
     * @param AttributeInterface $attribute attribute to generate a file identifier for
     * @param string $additional_prefix a string to add in front of the generated identifier
     * @param string $extension a (file) extension to append to the generated identifier (e.g. 'jpg')
     *
     * @return string unique relative file path
     */
    public static function generatePath(AttributeInterface $attribute, $additional_prefix = '', $extension = '');

    /**
     * Generates a unique file identifier with the given prefix (relative file path).
     *
     * @param string $prefix string the generated identifier should begin with (should not start with "/")
     * @param string $suffix string to append to the generated identifier (e.g. '.jpg')
     *
     * @return string unique relative file path
     */
    public static function generatePrefixedPath($prefix = 'type/attribute-name', $suffix = '');

    /**
     * Returns the default file extension known for the given mime type.
     *
     * @see Honeybee\Common\Util\FileToolkit::guessExtensionByMimeType
     *
     * @param string $mime_type well known internet media type
     * @param string $fallback_extension extension to return when no match was found
     *
     * @return string default file extension for given mime type or fallback extension provided
     */
    public function guessExtensionByMimeType($mime_type, $fallback_extension = '');

    /**
     * Returns the default mime type known for the given extension.
     *
     * @see Honeybee\Common\Util\FileToolkit::guessExtensionByMimeType
     *
     * @param string $extension
     * @param string $fallback_mime_type mime_type to return when no match was found
     *
     * @return string mime-type for given extension or the provided fallback
     */
    public function guessMimeTypeByExtension($extension, $fallback_mime_type = '');

    /**
     * Returns a file extension guessed for the given local file.
     *
     * @see Honeybee\Common\Util\FileToolkit::guessExtension
     *
     * @param string $file_path path to file
     * @param string $fallback_extension extension to return on failed guess
     *
     * @return string default file extension for given mime type or fallback extension provided
     */
    public function guessExtensionForLocalFile($file_path, $fallback_extension = '');

    /**
     * @return array all prefixes known to the filesystem service (e.g. [ 'files://', 'tempfiles://', … ])
     */
    public function getPrefixes();

    /**
     * @return array all scheme names known to the filesystem service (e.g. [ 'files', 'tempfiles', 's3', … ])
     */
    public function getSchemes();

    /**
     * Returns the name of the connector that is used to create a Filesystem
     * for the given scheme.
     *
     * @param string $scheme prefix of the filesystem to return
     *
     * @return string connector name that is used for the given scheme
     */
    public function getConnectorName($scheme);

    /**
     * @param string $scheme prefix of the filesystem to return
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    public function getFilesystem($scheme);
}
