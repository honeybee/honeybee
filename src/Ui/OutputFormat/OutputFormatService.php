<?php

namespace Honeybee\Ui\OutputFormat;

class OutputFormatService implements OutputFormatServiceInterface
{
    protected $output_format_map;

    public function __construct(OutputFormatMap $output_format_map)
    {
        $this->output_format_map = $output_format_map;
    }

    /**
     * @param string $name identifier of output format
     *
     * @return OutputFormatInterface|null
     */
    public function getOutputFormat($name)
    {
        return $this->output_format_map->getItem($name);
    }
}
