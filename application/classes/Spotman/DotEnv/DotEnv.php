<?php
declare(strict_types=1);

namespace Spotman\DotEnv;


class DotEnv
{
    private $records = [];

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

            $name = trim($matches[1]);

            if (!array_key_exists($name, $data)) {
                $newLines[] = $line;
                continue;
            }

            $value = $data[$name];

            $line = "$name=$value\n";
            $newLines[] = $line;
        }

        $newContent = implode('', $newLines);
        file_put_contents($envFile, $newContent);
    }

    /**
     * @param string $envFile
     *
     * @throws \Spotman\DotEnv\DotEnvException
     */
    public function load(string $envFile): void
    {
        $parser = new DotEnvParser();

        $content = file_get_contents($envFile);

        $this->records = $parser->parse($content);
    }
}
