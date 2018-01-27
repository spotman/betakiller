<?php
declare(strict_types=1);

namespace Spotman\DotEnv;


class DotEnvParser
{
    /**
     * @param string $content
     *
     * @return \Spotman\DotEnv\DotEnvRecord[]
     * @throws \Spotman\DotEnv\DotEnvException
     */
    public function parse(string $content): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $content);
        $records = [];

        foreach ($lines as $line) {
            $records[] = $this->parseLine($line);
        }

        return $records;
    }

    /**
     * @param string $line
     *
     * @return \Spotman\DotEnv\DotEnvRecord
     * @throws \Spotman\DotEnv\DotEnvException
     */
    private function parseLine(string $line): DotEnvRecord
    {
        // Detect comment if exists
        $hashPos = mb_strrpos($line, '#');

        if ($hashPos !== false) {
            $definition = mb_substr($line, 0, $hashPos);
            $comment = mb_substr($line, $hashPos);
        }


        if (!preg_match(DotEnvRecord::REGEX, $line, $matches, PREG_SET_ORDER)) {
            throw new DotEnvException('Can not parse line :value', [':value' => $line]);
        }

        var_dump($matches);
    }
}
