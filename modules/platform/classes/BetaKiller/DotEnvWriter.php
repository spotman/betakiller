<?php

declare(strict_types=1);

namespace BetaKiller;

class DotEnvWriter
{
    /**
     * @param string $envFile
     * @param array  $data
     *
     * @link https://stackoverflow.com/a/44448503/3640406
     */
    public function update(string $envFile, array $data): void
    {
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
        file_put_contents($envFile, $newContent);
    }
}
