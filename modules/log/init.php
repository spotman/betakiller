<?php

// Proxy old Kohana logs to new logging subsystem
Kohana::$log->attach(new \BetaKiller\Log\KohanaLogWriter());
