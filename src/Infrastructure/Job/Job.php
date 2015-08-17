<?php

namespace Honeybee\Infrastructure\Job;

use Trellis\Common\Object;
use Exception;

/**
 * @todo Jobs must be serializable, so we can do the loading of related entities inside the sleepup etc.
 * Maybe serving an init method is fine a well.
 */

abstract class Job extends Object implements JobInterface
{
    protected $state = self::STATE_FRESH;

    protected $errors = array();

    protected $max_retries = 3;

    abstract protected function execute();

    public function run(array $parameters = array())
    {
        try {
            $this->execute($parameters);
            $this->setState(self::STATE_SUCCESS);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();

            if ($this->getErrorCount() < $this->max_retries) {
                $this->setState(self::STATE_ERROR);
            } else {
                $this->setState(self::STATE_FATAL);
            }
        }

        return $this->state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        static $valid_states = array(
            self::STATE_FRESH,
            self::STATE_SUCCESS,
            self::STATE_ERROR,
            self::STATE_FATAL
        );

        if (!in_array($state, $valid_states)) {
            throw new Exception("Invalid state given.");
        }

        $this->state = $state;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorCount()
    {
        return count($this->errors);
    }
}
