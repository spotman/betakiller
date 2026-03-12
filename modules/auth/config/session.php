<?php

return [
    // DO NOT CHANGE 2 LINES BELOW - ALL PASSWORD HASHES in DB WOULD BECOME INCORRECT
    'encrypt_key' => \getenv('SESSION_ENCRYPT_KEY'),
    'hash_key'    => 'the_secret_hash_key_which_nobody_can_hack',
    'hash_method' => 'sha256',
    'lifetime'    => 1209600,
    'bind_to_ua'  => false,
];
