<?php

namespace Honeybee\Ui\Renderer\VndError;

use Honeybee\Ui\Renderer\GenericLink;
use Trellis\Common\Object;

class VndError extends Object
{
    protected $logref;
    protected $message;
    protected $help;
    protected $describes;
/*
    public function __construct($message, $logref = null, GenericLink $help = null, GenericLink $describes = null)
    {
        $this->message = $message;
        $this->logref = $logref;
        $this->help = $help;
        $this->describes = $describes;
    }
*/
    public function getLogref()
    {
        return $this->logref;
    }

    public function getMessage()
    {
        return $this->message;
    }

    protected function setHelp(GenericLink $help)
    {
        $this->help = $help;
    }

    public function getHelp()
    {
        return $this->help;
    }

    protected function setDescribes(GenericLink $describes)
    {
        $this->describes = $describes;
    }

    public function getDescribes()
    {
        return $this->describes;
    }

    public function getLinks()
    {
        $links = [];

        if (null !== $this->help) {
            $links[] = $this->help;
        }

        if (null !== $this->describes) {
            $links[] = $this->describes;
        }

        return $links;
    }
}
