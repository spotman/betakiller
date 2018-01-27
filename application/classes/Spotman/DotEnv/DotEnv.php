<?php
declare(strict_types=1);

namespace Spotman\DotEnv;


class DotEnv
{
    /**
     * @param string $envFile
     * @param array  $data
     * @link https://stackoverflow.com/a/44448503/3640406
     */
    public function update(string $envFile, array $data): void
    {
        $lines = file($envFile);
        $pattern = '/([^\=]*)\=[^\n]*/';

        $newLines = [];
        foreach ($lines as $line) {
            preg_match($pattern, $line, $matches);

            if (!\count($matches)) {
                $newLines[] = $line;
                continue;
            }

            if (!array_key_exists(trim($matches[1]), $data)) {
                $newLines[] = $line;
                continue;
            }

            $line = trim($matches[1]) . "={$data[trim($matches[1])]}\n";
            $newLines[] = $line;
        }

        $newContent = implode('', $newLines);
        file_put_contents($envFile, $newContent);
    }
}
