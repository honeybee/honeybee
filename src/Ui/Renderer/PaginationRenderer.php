<?php

namespace Honeybee\Ui\Renderer;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Ui\ValueObjects\PaginationInterface;
use Honeybee\Common\Util\ArrayToolkit;

abstract class PaginationRenderer extends Renderer
{
    protected function validate()
    {
        if (!$this->getPayload('subject') instanceof PaginationInterface) {
            throw new RuntimeError(
                sprintf('Payload "subject" must implement "%s".', PaginationInterface::CLASS)
            );
        }
    }

    protected function doRender()
    {
        return $this->getTemplateRenderer()->render($this->getTemplateIdentifier(), $this->getTemplateParameters());
    }

    protected function getDefaultTemplateIdentifier()
    {
        return $this->output_format->getName() . '/pagination/pagination.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $pagination = $this->getPayload('subject');

        $params = array_merge($params, $pagination->toArray());

        $current_page_url = $this->genUrl(null);
        $current_url_parameters = ArrayToolkit::getUrlQueryInRequestFormat($current_page_url);
        unset($current_url_parameters['offset']); // offset is not needed when page is used (see validator)

        $url_parameters = (array)$this->getOption('url_parameters', []);
        $url_parameters = array_merge($current_url_parameters, $url_parameters);
        // we add all query parameters of the current URL as hidden GET form inputs to not lose any state on submit
        $params['url_parameters'] = $url_parameters;

        $params['current_page_url'] = $this->genUrl(null, $url_parameters);

        $params['first_page_url'] = $this->genUrl(null, array_merge($url_parameters, [ 'offset' => 0 ]));

        $params['last_page_url'] = $this->genUrl(
            null,
            array_merge($url_parameters, [ 'offset' => $pagination->getLastPageOffset() ])
        );

        $params['next_page_url'] = $this->genUrl(
            null,
            array_merge($url_parameters, [ 'offset' => $pagination->getNextPageOffset() ])
        );

        $params['prev_page_url'] = $this->genUrl(
            null,
            array_merge($url_parameters, [ 'offset' => $pagination->getPrevPageOffset() ])
        );

        $params['number_of_pages'] = $pagination->getNumberOfPages();

        return $params;
    }

    protected function getDefaultTranslationDomain()
    {
        return sprintf('%s.pagination', parent::getDefaultTranslationDomain());
    }
}
