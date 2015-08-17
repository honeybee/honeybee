<?php

namespace Honeybee\Ui;

/**
 * Interface to support internationalisation by covering to following aspects:
 *
 * - translating messages
 * - translating/formatting numbers
 * - translating/formatting currencies
 * - translating/formatting dates
 *
 * Implementations of this interface will probably not be compatible for
 * all aspects covered as e.g. message formatting syntax differs.
 *
 * As the Honeybee renderers and twig templates being used by applications
 * are exchangable this may be a solvable problem for the projects.
 * PHP Intl, ICU stuff like formatting and parsing as well as differing levels
 * of support by application frameworks will make it hard to switch application
 * level frameworks while this interface hopefully still helps with the process.
 *
 * The locale string given to the methods of this interface identify the target
 * locale to use. The identifier should be either a RFC 4646 language tag using
 * hyphens or a combination of ISO 639-1 language code, an underscore and then
 * the ISO 3166-1 alpha-2 country code. The implementing classes should thus be
 * tolerant of versions like 'de-at' and 'de_AT'. The language country combination
 * may be preferable if in doubt of what to support.
 *
 * The messages given to the methods may be actual real messages or keyword messages
 * that convey the idea of the actual message. Real messages have the advantage of
 * basically being the first translation while having the disadvantage of breaking
 * the translation when you change that message. The keyword messages are more robust
 * to changes but have the disadvantage of needing to provide even the first
 * translation. For single locale applications the real messages might be fine for
 * internationalisation while for projects with localisation needs coming up the
 * use of keyword messages might be preferable.
 *
 * For more information on ICU, CLDR or gettext use the following as a start:
 *
 * @see http://site.icu-project.org/
 * @see http://cldr.unicode.org/
 * @see http://www.gnu.org/software/gettext/manual/
 *
 * Translating and formatting as it is handled in different frameworks:
 *
 * @see http://php.net/manual/en/book.intl.php
 * @see https://symfony.com/doc/current/book/translation.html
 * @see http://framework.zend.com/manual/current/en/modules/zend.i18n.translating.html
 * @see http://www.agavi.org/apidocs/db_translation_AgaviTranslationManager.class.html
 * @see http://laravel.com/docs/4.2/localization
 *
 * @todo think about the "fallback" argument and the plural stuff
 */
interface TranslatorInterface
{
    /**
     * Translate a message into the current or given locale.
     *
     * @param string $message message or message identifier to be translated
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to translate into
     * @param array $params parameters to use for translation
     * @param string $fallback text to use when translation fails
     *
     * @return string translated message
     */
    public function translate($message, $domain = null, $locale = null, array $params = null, $fallback = null);

    /**
     * Translate a singular/plural message into the current or given locale.
     *
     * @param string $message_singular message or message identifier for the singular form
     * @param string $message_plural message or message identifier for the plural form
     * @param int $amount amount to use for translation
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to translate into
     * @param array $params parameters to use for translation
     * @param string $fallback_singular text to use when translation fails
     * @param string $fallback_plural text to use when translation fails
     *
     * @return string translated message
     */
    public function translatePlural(
        $message_singular,
        $message_plural,
        $amount,
        $domain = null,
        $locale = null,
        array $params = null,
        $fallback_singular = null,
        $fallback_plural = null
    );

    /**
     * Formats a date/datetime in the current or given locale.
     *
     * @param mixed $date date or datetime to be formatted
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to use for formatting
     *
     * @return string formatted date
     */
    public function translateDate($date, $domain = null, $locale = null);

    /**
     * Formats a currency amount in the current or given locale.
     *
     * @param mixed $currency currency to be formatted
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to use for formatting
     *
     * @return string formatted currency
     */
    public function translateCurrency($currency, $domain = null, $locale = null);

    /**
     * Formats a number in the current locale.
     *
     * @param mixed $number number to be formatted
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to use for formatting
     *
     * @return string formatted number
     */
    public function translateNumber($number, $domain = null, $locale = null);
}
