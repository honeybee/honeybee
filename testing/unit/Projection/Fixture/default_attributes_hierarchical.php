<?php
return [
    'identifier' => [
        '@type' => 'Trellis\Runtime\Attribute\Text\TextAttribute',
        'parent' => null,
        'name' => 'identifier',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'revision' => [
        '@type' => 'Trellis\Runtime\Attribute\Integer\IntegerAttribute',
        'parent' => null,
        'name' => 'revision',
        'options' => [ 'default_value' => 0 ],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'uuid' => [
        '@type' => 'Trellis\Runtime\Attribute\Uuid\UuidAttribute',
        'parent' => null,
        'name' => 'uuid',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'language' => [
        '@type' => 'Trellis\Runtime\Attribute\Text\TextAttribute',
        'parent' => null,
        'name' => 'language',
        'options' => [ 'default_value' => 'de_DE' ],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'version' => [
        '@type' => 'Trellis\Runtime\Attribute\Integer\IntegerAttribute',
        'parent' => null,
        'name' => 'version',
        'options' => [ 'default_value' => 1 ],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'created_at' => [
        '@type' => 'Trellis\Runtime\Attribute\Timestamp\TimestampAttribute',
        'parent' => null,
        'name' => 'created_at',
        'options' => [
            'force_internal_timezone' => true,
            'internal_timezone_name' => 'UTC'
        ],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'modified_at' => [
        '@type' => 'Trellis\Runtime\Attribute\Timestamp\TimestampAttribute',
        'parent' => null,
        'name' => 'modified_at',
        'options' => [
            'force_internal_timezone' => true,
            'internal_timezone_name' => 'UTC'
        ],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'workflow_state' => [
        '@type' => 'Trellis\Runtime\Attribute\Text\TextAttribute',
        'parent' => null,
        'name' => 'workflow_state',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'workflow_parameters' => [
        '@type' => 'Trellis\Runtime\Attribute\KeyValueList\KeyValueListAttribute',
        'parent' => null,
        'name' => 'workflow_parameters',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'metadata' => [
        '@type' => 'Trellis\Runtime\Attribute\KeyValueList\KeyValueListAttribute',
        'parent' => null,
        'name' => 'metadata',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'parent_node_id' => [
        '@type' => 'Trellis\Runtime\Attribute\Text\TextAttribute',
        'parent' => null,
        'name' => 'parent_node_id',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ],
    'materialized_path' => [
        '@type' => 'Trellis\Runtime\Attribute\Text\TextAttribute',
        'parent' => null,
        'name' => 'materialized_path',
        'options' => [],
        'validator' => null,
        'value_holder_implementor' => null
    ]
];
