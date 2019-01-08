<?php

namespace Honeybee\Infrastructure\Filesystem;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\FileToolkit;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\EntityTypeInterface;
use League\Flysystem\MountManager;
use Ramsey\Uuid\Uuid as UuidGenerator;
use Trellis\Runtime\Attribute\AttributeInterface;

/**
 * Service that encapsulates the handling of files. All calls to non-existing methods
 * will be forwarded to a configured Flysytem MountManager instance.
 *
 * This service supports the creation of URIs for the mount manager for given
 * file identifiers and aggregate root types.
 *
 * @see League\Flysystem\MountManager
 */
class FilesystemService implements FilesystemServiceInterface
{
    protected $mount_manager;
    protected $schemes;

    /**
     * @param MountManager $mount_manager
     * @param array $schemes map of scheme and connector name (e.g. "files" => "Assets.Local")
     */
    public function __construct(MountManager $mount_manager, array $schemes)
    {
        $this->mount_manager = $mount_manager;
        $this->schemes = $schemes;
    }

    /**
     * Copies a file from one location to the other.
     *
     * To create the URIs for a given aggregate root use the createUri or createTempUri methods.
     * Those methods create URIs that use an aggregate root specific scheme.
     *
     * @param string $from_uri source location (including prefix) of file to copy, e.g. "tempfiles://foo.jpg"
     * @param string $to_uri target location (including prefix), e.g. "files://some/path/bar.jpg"
     *
     * @return boolean true on success
     */
    public function copy($from_uri, $to_uri)
    {
        return $this->mount_manager->copy($from_uri, $to_uri);
    }

    /**
     * List the contents of a directory.
     *
     * @param string $uri location of the folder to list
     * @param boolean $recursive whether to include all children recursively
     *
     * @return array of file information
     */
    public function listContents($uri = '', $recursive = false)
    {
        return $this->mount_manager->listContents($uri, $recursive);
    }

    /**
     * Check whether a file exists.
     *
     * @param string $uri location of the file (including prefix)
     *
     * @return boolean true if the uri target exists; false otherwise.
     */
    public function has($uri)
    {
        return $this->mount_manager->has($uri);
    }

    /**
     * Get the filesize of a file or directory.
     *
     * @param string $uri location of file including prefix
     *
     * @return int|false size of file in bytes
     */
    public function getSize($uri)
    {
        return $this->mount_manager->getSize($uri);
    }

    /**
     * Get the mime type of a file (usually without the encoding information).
     *
     * @param string $uri location of the file to examine (including prefix)
     *
     * @return string|false mime type
     */
    public function getMimetype($uri)
    {
        return $this->mount_manager->getMimetype($uri);
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $uri location of the file to examine (including prefix)
     *
     * @return int|false
     */
    public function getTimestamp($uri)
    {
        return $this->mount_manager->getTimestamp($uri);
    }

    /**
     * Read an existing file as a stream.
     *
     * @param string $uri location of the file (including prefix)
     *
     * @return resource|false
     */
    public function readStream($uri)
    {
        return $this->mount_manager->readStream($uri);
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $uri location to write to (including prefix)
     * @param resource $resource resource to read from
     * @param array $config options
     *
     * @return boolean true on success; false otherwise
     */
    public function writeStream($uri, $resource, array $config = [])
    {
        return $this->mount_manager->writeStream($uri, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $from_uri source location (including prefix)
     * @param string $to_uri target location (including prefix)
     *
     * @return boolean true on successful renaming
     */
    public function rename($from_uri, $to_uri)
    {
        return $this->mount_manager->rename($from_uri, $to_uri);
    }

    /**
     * Delete a file.
     *
     * @param string $uri location of the file to remove (including prefix)
     *
     * @return boolean true on successful deletion
     */
    public function delete($uri)
    {
        return $this->mount_manager->delete($uri);
    }

    /**
     * Delete a directory.
     *
     * @param string $uri location (directory) to delete (including prefix)
     *
     * @return boolean true on successful deletion of the folder
     */
    public function deleteDir($uri)
    {
        return $this->mount_manager->deleteDir($uri);
    }

    /**
     * Create a directory.
     *
     * @param string $uri location (directory) to create (including prefix)
     * @param array $config options
     *
     * @return boolean true on successful creation
     */
    public function createDir($uri, array $config = [])
    {
        return $this->mount_manager->createDir($uri, $config);
    }

    /**
     * Creates a URI to the main asset/file storage for the given aggregate root type.
     * Defaults to the common scheme ('files') when no aggregate root type is given.
     *
     * @param string $relative_file_path identifier of the file to work with
     * @param EntityTypeInterface $entity_type type to use for filesystem scheme generation
     *
     * @return string URI for the given file, e.g. "files://relative/file/path"
     */
    public function createUri($relative_file_path, EntityTypeInterface $entity_type = null)
    {
        return $this->getScheme($entity_type) . self::SCHEME_SEPARATOR . $relative_file_path;
    }

    /**
     * Creates a URI to the temporary asset/file storage for the given aggregate root type.
     * Defaults to the common scheme ("tempfiles") when no aggregate root type is given.
     *
     * @param string $relative_file_path identifier of the file to work with
     * @param EntityTypeInterface $entity_type type to use for filesystem scheme generation
     *
     * @return string URI for the given file, e.g. "tempfiles://relative/file/path"
     */
    public function createTempUri($relative_file_path, EntityTypeInterface $entity_type = null)
    {
        return $this->getTempScheme($entity_type) . self::SCHEME_SEPARATOR . $relative_file_path;
    }

    /**
     * Returns the prefix that is used for access of the main file/asset storage for
     * the given aggregate root type. Without given type the default prefix for the
     * common file/asset storage is returned ("files://").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string scheme with separator, e.g. 'files://' or 'userfiles://'
     */
    public function getPrefix(EntityTypeInterface $entity_type = null)
    {
        return $this->getScheme($entity_type) . self::SCHEME_SEPARATOR;
    }

    /**
     * Returns the prefix that is used for access of the temporary file/asset storage
     * for the given aggregate root type. When no type is given the default prefix
     * for the common temporary file/asset storage is returned ("tempfiles://").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string prefix, e.g. 'tempfiles://' or 'usertempfiles://'
     */
    public function getTempPrefix(EntityTypeInterface $entity_type = null)
    {
        return $this->getTempScheme($entity_type) . self::SCHEME_SEPARATOR;
    }

    /**
     * Returns the scheme that is used for access of the main file/asset storage for
     * the given aggregate root type. When no type is given the default scheme name
     * for the common file/asset storage is returned ("files").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string scheme name, e.g. 'files' or 'userfiles'
     */
    public function getScheme(EntityTypeInterface $entity_type = null)
    {
        if (null === $entity_type) {
            return self::SCHEME_FILES;
        }

        return $entity_type->getPrefix() . '.' . self::SCHEME_FILES;
    }

    /**
     * Returns the scheme that is used for access of the temporary file/asset storage for
     * the given aggregate root type. When no type is given the default scheme name
     * for the common temporary file/asset storage is returned ("tempfiles").
     *
     * @param EntityTypeInterface $entity_type aggregate root type instance
     *
     * @return string scheme name, e.g. 'tempfiles' or 'usertempfiles'
     */
    public function getTempScheme(EntityTypeInterface $entity_type = null)
    {
        if (null === $entity_type) {
            return self::SCHEME_TEMPFILES;
        }

        return $entity_type->getPrefix() . '.' . self::SCHEME_TEMPFILES;
    }

    /**
     * Generates a unique file identifier that may be used as a relative file path.
     *
     * @param AttributeInterface $attribute attribute to generate a file identifier for
     * @param string $additional_prefix a string to add in front of the generated identifier
     * @param string $extension a (file) extension to append to the generated identifier (e.g. 'jpg')
     *
     * @return string unique relative file path
     */
    public static function generatePath(
        AttributeInterface $attribute,
        $additional_prefix = '',
        $extension = '',
        $uuid = null
    ) {
        $uuid = $uuid ? UuidGenerator::fromString($uuid) : UuidGenerator::uuid4();
        $uuid_string = $uuid->toString();
        $uuid_parts = $uuid->getClockSeqLow(); // 8 bit int => 256 folders

        $root_type = $attribute->getRootType();
        $root_type_name = $root_type->getName();
        if ($root_type instanceof EntityTypeInterface) {
            $root_type_name = $root_type->getPrefix();
        }

        $attribute_path = $attribute->getPath();

        $identifier = '';

        if (!empty($additional_prefix)) {
            $identifier = $additional_prefix . '/';
        }

        $identifier .= sprintf('%s/%s/%s/%s', $root_type_name, $attribute_path, $uuid_parts, $uuid_string);

        if (!empty($extension)) {
            $identifier .= '.' . $extension;
        }

        return $identifier;
    }

    /**
     * Generates a unique file identifier with the given prefix (relative file path).
     *
     * @param string $prefix string the generated identifier should begin with (should not start with "/")
     * @param string $suffix string to append to the generated identifier (e.g. '.jpg')
     *
     * @return string unique relative file path
     */
    public static function generatePrefixedPath($prefix = 'type/attribute-name', $suffix = '')
    {
        $uuid = UuidGenerator::uuid4();
        $uuid_string = $uuid->toString();
        $uuid_parts = $uuid->getClockSeqLow(); //str_replace('-', '/', $uuid);

        return sprintf('%s/%s/%s%s', $prefix, $uuid_parts, $uuid, $suffix);
    }

    /**
     * @return array all prefixes known to the filesystem service (e.g. [ 'files://', 'tempfiles://', … ])
     */
    public function getPrefixes()
    {
        $prefixes = [];
        foreach ($this->getSchemes() as $scheme) {
            $prefixes[] = $scheme . self::SCHEME_SEPARATOR;
        }

        return $prefixes;
    }

    /**
     * @return array all scheme names known to the filesystem service (e.g. [ 'files', 'tempfiles', 's3', … ])
     */
    public function getSchemes()
    {
        return array_keys($this->schemes);
    }

    /**
     * @param string $scheme prefix of the filesystem to return
     *
     * @return string name of connector that is used for the given scheme
     */
    public function getConnectorName($scheme)
    {
        if (!array_key_exists($scheme, $this->schemes)) {
            throw new RuntimeError('No filesystem connector configured for scheme (prefix): ' . $scheme);
        }

        return $this->schemes[$scheme];
    }

    /**
     * @param string $scheme prefix of the filesystem to return
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    public function getFilesystem($scheme)
    {
        if (empty($scheme) || !is_string($scheme)) {
            throw new RuntimeError('Scheme (prefix) of filesystem must be a non-empty string.');
        }

        if (!StringToolkit::endsWith($scheme, self::SCHEME_SEPARATOR)) {
            $scheme .= self::SCHEME_SEPARATOR;
        }

        return $this->mount_manager->getAdapter($scheme);
    }

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
    public function guessExtensionByMimeType($mime_type, $fallback_extension = '')
    {
        return FileToolkit::guessExtensionByMimeType($mime_type, $fallback_extension);
    }

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
    public function guessMimeTypeByExtension($extension, $fallback_mime_type = '')
    {
        return FileToolkit::guessMimeTypeByExtension($extension, $fallback_mime_type);
    }

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
    public function guessExtensionForLocalFile($file_path, $fallback_extension = '')
    {
        return FileToolkit::guessExtensionForLocalFile($file_path, $fallback_extension);
    }

    /**
     * Forward all calls to the internal MountManager instance.
     *
     * @see League\Flysystem\MountManager
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->mount_manager, $method], $arguments);
    }
}
