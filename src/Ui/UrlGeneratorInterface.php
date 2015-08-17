<?php

namespace Honeybee\Ui;

interface UrlGeneratorInterface
{
    /**
     * Generates a URL for the given route name and parameters. The options
     * MAY be used to provide hints for the URL generation. This includes
     * information about different parts of a URL, e.g. the scheme or port to
     * use or whether absolute or relative URLs should be generated.
     *
     * The usual URL has the following simplified structure:
     *
     * scheme://userinfo@host:port/path?query#fragment
     *
     * @see http://en.wikipedia.org/wiki/URI_scheme
     * @see http://tools.ietf.org/html/rfc3986
     * @see http://tools.ietf.org/html/std66
     *
     * Suggested option keys and their default values are:
     *
     * - 'relative': false
     *     whether to generate absolute or relative URLs
     * - 'separator': '&'
     *     query parameters separator, e.g. ';' – see ini_get('arg_separator.output')
     * - 'scheme': null
     *     scheme name, e.g. 'ftp' – true/false to include/exclude;
     *     use an empty string ('') to generate protocol relative URLs ('//host.tld')
     * - 'userinfo': null
     *     user information string, e.g. 'user:pwd' – true/false to include/exclude
     * - 'host': null
     *     host string, e.g 'www.example.org' – true/false to include/exclude
     * - 'port': null
     *     port string, e.g. '8080' – true/false to include/exclude
     * - 'authority': null
     *     authority string, e.g. 'user:info@host:port' – true/false to include/exclude
     * - 'path': null
     *     path string, e.g. 'some/hierarchical/path' – true/false to include/exclude
     * - 'fragment': null
     *     fragment identifier string, e.g. 'foo' – true/false to include/exclude
     * - 'use_trans_sid': false
     *     whether or not to include a session id (SID) – see ini_get('session.use_trans_sid')
     *
     * Other options may be used as well. Adapters for frameworks should
     * use and convert those options to their respective implementations of
     * handling the generation of URLs.
     *
     * While this interface is primarily intended to generate URLs, it SHOULD be
     * possible to create any URIs depending on the implementations and the
     * provided options.
     *
     * @param string $name route name or lookup key to generate an URL for
     * @param array $parameters pairs of placeholder names and values
     * @param array $options array of options to influence URL generation
     *
     * @return string URL relative or absolute URL
     */
    public function generateUrl($name, array $parameters = [], array $options = []);
}
