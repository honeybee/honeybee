<?php

namespace Honeybee\Infrastructure\Template\Twig\Extension;

use Honeybee\Ui\TranslatorInterface;
use Twig_Extension;
use Twig_Filter_Method;
use Twig_Function_Method;

/**
 * Extension that wraps the TranslatorInterface methods to make them available in twig templates.
 */
class TranslatorExtension extends Twig_Extension
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            'translate' => new Twig_Filter_Method($this, 'translate'),
            'translateCurrency' => new Twig_Filter_Method($this, 'translateCurrency'),
            'translateNumber' => new Twig_Filter_Method($this, 'translateNumber'),
            'translateDate' => new Twig_Filter_Method($this, 'translateDate'),
            'translatePlural' => new Twig_Filter_Method($this, 'translatePlural'),
        );
    }

    public function getFunctions()
    {
        return array(
            '_' => new Twig_Function_Method($this, 'translate'),
            '_c' => new Twig_Function_Method($this, 'translateCurrency'),
            '_n' => new Twig_Function_Method($this, 'translateNumber'),
            '_d' => new Twig_Function_Method($this, 'translateDate'),
            '__' => new Twig_Function_Method($this, 'translatePlural'),
        );
    }

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
    public function translate($message, $domain = null, $locale = null, array $params = null, $fallback = null)
    {
        return $this->translator->translate($message, $domain, $locale, $params, $fallback);
    }

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
    ) {
        return $this->translator->translatePlural(
            $message_singular,
            $message_plural,
            $amount,
            $domain,
            $locale,
            $params,
            $fallback_singular,
            $fallback_plural
        );
    }

    /**
     * Formats a date/datetime in the current or given locale.
     *
     * @param mixed $date date or datetime to be formatted
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to use for formatting
     *
     * @return string formatted date
     */
    public function translateDate($date, $domain = null, $locale = null)
    {
        return $this->translator->translateDate($date, $domain, $locale);
    }

    /**
     * Formats a currency amount in the current or given locale.
     *
     * @param mixed $currency currency to be formatted
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to use for formatting
     *
     * @return string formatted currency
     */
    public function translateCurrency($currency, $domain = null, $locale = null)
    {
        return $this->translator->translateCurrency($currency, $domain, $locale);
    }

    /**
     * Formats a number in the current locale.
     *
     * @param mixed $number number to be formatted
     * @param string $domain domain to use for translation
     * @param string $locale identifier of the locale to use for formatting
     *
     * @return string formatted number
     */
    public function translateNumber($number, $domain = null, $locale = null)
    {
        return $this->translator->translateNumber($number, $domain, $locale);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string extension name.
     */
    public function getName()
    {
        return static::CLASS;
    }
}
