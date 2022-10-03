<?php

/*
unique-lock
    table-name => field to unique
    table-name-foreign => another field yang ditambahkan sebagai kondisi tambahan
*/

return [
    'unique-lock' => [
            'users' => ['name'],
            'permalinks' => ['permalink']
        ]
    ];