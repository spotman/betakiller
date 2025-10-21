<?php

declare(strict_types=1);

namespace BetaKiller;

class DotEnvWriter
{
    /**
     * @param string $envFile
     * @param array  $data
     *
     * @throws \BetaKiller\Exception
     * @link https://stackoverflow.com/a/44448503/3640406
     */
    public function update(string $envFile, array $data): void
    {
        if (!file_exists($envFile)) {
            throw new Exception('Missing .env file at ":path"', [
                ':path' => $envFile,
            ]);
        }

        if (!is_readable($envFile)) {
            throw new Exception('.env file is not readable at ":path"', [
                ':path' => $envFile,
            ]);
        }

        if (!is_writable($envFile)) {
            throw new Exception('.env file is not writable at ":path"', [
                ':path' => $envFile,
            ]);
        }

        $lines   = file($envFile);
        $pattern = '/([^=]*)=[^\n]*/';

        $newLines = [];
        foreach ($lines as $line) {
            preg_match($pattern, $line, $matches);

            if (!$matches) {
                $newLines[] = $line;
                continue;
            }

            $name = trim($matches[1]);

            if (!array_key_exists($name, $data)) {
                $newLines[] = $line;
                continue;
            }

            $value = $data[$name];

            $line       = "$name=$value\n";
            $newLines[] = $line;
        }

        $newContent = implode('', $newLines);

        if (!file_put_contents($envFile, $newContent, LOCK_EX)) {
            throw new Exception('.env file was not updated at ":path"', [
                ':path' => $envFile,
            ]);
        }
    }
}
