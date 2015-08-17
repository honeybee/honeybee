<?php

namespace Honeybee\Model\Command\Bulk;

use Trellis\Common\Object;

class BulkStreamError extends Object
{
    const INVALID_META_DATA = 'invalid_meta_data';

    const INVALID_PAYLOAD = 'invalid_payload';

    const INVALID_FORMAT = 'invalid_format';

    const EOF = 'end_of_stream';

    protected $type;

    protected $message;

    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
