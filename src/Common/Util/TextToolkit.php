<?php

namespace Honeybee\Infrastructure\Export\Filter;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;

/**
 * The TextToolkit class provides some convenience methods to act upon a given text,
 * such as fetching all full sentences, certain number of characters, specific word ranges, etc.
 */
class TextToolkit
{
    protected $config;

    protected $sentence_chunker;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getCharacters($text, $count = 0, $offset = 0)
    {
        if ($count === 0) {
            $text = mb_substr($text, $offset);
        } else {
            $text = mb_substr($text, $offset, $count);
        }

        return mb_substr($text, 0, strrpos($text, " "));
    }

    public function getWords($text, $count = 0, $offset = 0)
    {
        $words = explode(' ', $text);

        if ($count === 0) {
            $words = array_slice($words, $offset);
        } else {
            $words = array_slice($words, $offset, $count);
        }

        return implode(' ', $words);
    }

    public function getSentences($text, $count = 0, $offset = 0)
    {
        $chunker = $this->getSenctenceChunker();
        $sentences = $chunker->chunk($text);

        if ($sentences && count($sentences) > $offset) {
            if ($count <= 0) {
                return array_slice($sentences, $offset);
            } else {
                $count = min(count($sentences) - 1, $count);
                return array_slice($sentences, $offset, $count);
            }
        }

        return false;
    }

    public function getParagraphs($text, $offset = 0, $count = 0)
    {
        $text = implode('</p>', array_slice(explode('</p>', $text), $offset, $count)) . '</p>';
        $text = preg_replace('#^(.*?)<p#', '<p', $text); // strip content before first paragraph

        return preg_replace('#</p>(.*?)<p#', '</p><p', $text); // strip content between paragraphs
    }

    public function stripNewlines($text)
    {
        return preg_replace('#\n|\r|\r\n|\n\r#', '', $text);
    }

    public function stripMultipleSpaces($text)
    {
        return preg_replace('#\s{2,}#', ' ', $text);
    }

    public function stripTabs($text)
    {
        return preg_replace('#\t#', '', $text);
    }

    public function stripTags($text, $allowed_tags = '')
    {
        $text = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $text);
        $text = strip_tags($text, $allowed_tags);

        return  preg_replace('#\s{2,}#', ' ', $text);
    }

    protected function getSenctenceChunker()
    {
        if (!$this->sentence_chunker) {
            $dot_tokens_file = realpath($this->config->get('dot_tokens_file'));
            $this->sentence_chunker = new SentenceChunker(
                new ArrayConfig(array('dot_tokens_file' => $dot_tokens_file))
            );
        }

        return $this->sentence_chunker;
    }
}
