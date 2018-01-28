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
        if (!preg_match(DotEnvRecord::REGEX, $line, $matches, PREG_SET_ORDER)) {
            throw new DotEnvException('Can not parse line :value', [':value' => $line]);
        }

        // Detect comment if exists
        $hashPos = mb_strrpos($line, '#');

        $comment = $hashPos !== false ? trim(mb_substr($line, $hashPos), '# ') : null;

        // This is comment line
        if ($hashPos === 0 && $comment) {
            return new DotEnvRecord(null, null, $comment);
        }

        // Regular definition
        $definition = mb_substr($line, 0, $comment ? $hashPos : null);

        // Detect assignment operator
        $hashPos = mb_strpos($line, '=');

        if (!$hashPos) {
            throw new DotEnvException('Can not find assignment operator in line :value', [':value' => $line]);
        }

        $name = trim(mb_substr($definition, 0, $hashPos));
        $value = trim(mb_substr($definition, $hashPos));

        return new DotEnvRecord($name, $value, $comment);
    }
}
