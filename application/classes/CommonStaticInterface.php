<?php


interface CommonStaticInterface
{
    /**
     * Include local file from static-files directory
     *
     * @param string $filename
     *
     * @return string HTML code
     */
    public function addStatic(string $filename): string;
}
