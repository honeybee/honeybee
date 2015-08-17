<?php

namespace Honeybee\Ui\Renderer\VndError;

use Honeybee\Ui\Renderer\Renderer;
use Trellis\Common\Error\RuntimeException;

// @TODO this should probably reuse the HAL renderer, as the spec is compatible
// according to documentation and would thus allow for multiple errors to be
// returned as _embedded collection or so
class VndErrorJsonRenderer extends Renderer
{
    public function validate()
    {
        if (!$this->payload instanceof VndError) {
            new RuntimeException('Invalid payload type given. Expecting instance of VndError.');
        }

        $message = $this->payload->getMessage();
        if (empty($message)) {
            throw new RuntimeException('Setting a "message" is mandatory for VndError+json representations.');
        }
    }

    public function doRender()
    {
        $data = [];

        $logref = $this->payload->getLogref();
        if (!empty($logref)) {
            $data['logref'] = $logref;
        }

        $data['message'] = $this->payload->getMessage();

        $links = $this->payload->getLinks();
        if (count($links) > 0) {
            $links_data = array();
            foreach ($links as $link) {
                $links_data[$link->getFirstRel()] = [
                    'href' => $link->getUri()
                ];
                foreach($link->getAttributes() as $attr_name => $attr_value) {
                    $links_data[$link->getFirstRel()][$attr_name] = $attr_value;
                }
            }
            $data['_links'] = $links_data;
        }

        $json_options = 0;
        if (version_compare(PHP_VERSION, '5.4.0') >= 0 && $this->settings->get('pretty_print', false)) {
            $json_options |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $json_options);
    }
}
