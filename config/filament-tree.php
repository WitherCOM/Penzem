<?php

return [
    /**
     * Tree model fields
     */
    'column_name' => [
        'order' => 'id',
        'parent' => 'parent_id',
        'title' => 'name',
    ],
    /**
     * Tree model default parent key
     */
    'default_parent_id' => null,
    /**
     * Tree model default children key name
     */
    'default_children_key_name' => 'children',
];
