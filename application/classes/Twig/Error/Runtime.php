<?php

class Twig_Error_Runtime_ extends Twig_Error
{
    /**
     * Constructor.
     *
     * Set both the line number and the filename to false to
     * disable automatic guessing of the original template name
     * and line number.
     *
     * Set the line number to -1 to enable its automatic guessing.
     * Set the filename to null to enable its automatic guessing.
     *
     * By default, automatic guessing is enabled.
     *
     * @param string    $message  The error message
     * @param integer   $lineno   The template line where the error occurred
     * @param string    $filename The template file name where the error occurred
     * @param Exception $previous The previous exception
     */
    public function __construct($message, $lineno = -1, $filename = null, Exception $previous = null)
    {
        if ($previous) {
            // Re-throw for better debug
            throw $previous;
        }

        parent::__construct($message, $lineno, $filename);
    }
}
