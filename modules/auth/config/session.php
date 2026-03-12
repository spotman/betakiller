<?php

use BetaKiller\Config\SessionConfig;

return [
    // DO NOT CHANGE 2 LINES BELOW - ALL PASSWORD HASHES in DB WOULD BECOME INCORRECT
    SessionConfig::ENCRYPT_KEY => \getenv('SESSION_ENCRYPT_KEY'),
    SessionConfig::HASH_KEY    => 'the_secret_hash_key_which_nobody_can_hack',
    SessionConfig::HASH_METHOD => 'sha256',
    SessionConfig::LIFETIME    => 1209600,
    SessionConfig::BIND_TO_UA  => false,
];
