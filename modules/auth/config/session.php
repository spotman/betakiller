<?php

return [
    // DO NOT CHANGE 2 LINES BELOW - ALL PASSWORD HASHES in DB WOULD BECOME INCORRECT
    'hash_key'            => 'the_secret_hash_key_which_nobody_can_hack',
    'hash_method'         => 'sha256',
    'lifetime'            => 1209600,
    'allowed_class_names' => [],
    'encrypt_key'         => \getenv('SESSION_ENCRYPT_KEY'),
];
