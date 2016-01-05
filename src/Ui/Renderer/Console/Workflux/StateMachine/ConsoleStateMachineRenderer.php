<?php

namespace Honeybee\Ui\Renderer\Console\Workflux\StateMachine;

use Honeybee\Ui\Renderer\StateMachineRenderer;
use Workflux\Renderer\DotGraphRenderer;
use Symfony\Component\Process\Process;
use Honeybee\Common\Error\RuntimeError;

class ConsoleStateMachineRenderer extends StateMachineRenderer
{
    protected function doRender()
    {
        $renderer = new DotGraphRenderer();
        $dot_graph = $renderer->renderGraph($this->state_machine);

        $process = new Process('dot -Tsvg');
        $process->setInput($dot_graph);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeError($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
