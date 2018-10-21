<?php

use BetaKiller\View\ViewInterface;

final class View implements ViewInterface
{
    /**
     * Returns a new View object. If you do not define the "file" parameter,
     * you must call [View::setFilename].
     *
     *     $view = View::factory($file);
     *
     * @param   string $file view filename
     *
     * @return  static
     * @throws \View_Exception
     */
    public static function factory($file)
    {
        return new self($file);
    }

    /**
     * Captures the output that is generated when a view is included.
     * The view data will be extracted to make local variables. This method
     * is static to prevent object scope resolution.
     *
     *     $output = View::capture($file, $data);
     *
     * @param   string $viewFilename filename
     * @param   array  $viewData     variables
     *
     * @return  string
     */
    private static function capture(string $viewFilename, array $viewData): string
    {
        // Import the view variables to local namespace
        extract($viewData, EXTR_SKIP);

        // Capture the view output
        ob_start();

        try {
            // Load the view within the current scope
            /** @noinspection PhpIncludeInspection */
            include $viewFilename;
        } catch (Exception $e) {
            // Delete the output buffer
            ob_end_clean();

            // Re-throw the exception
            throw $e;
        }

        // Get the captured output and close the buffer
        return ob_get_clean();
    }

    // View filename
    private $file;

    // Array of local variables
    private $data = [];

    /**
     * Sets the initial view filename and local data. Views should almost
     * always only be created using [View::factory].
     *
     *     $view = new View($file);
     *
     * @param   string $file view filename
     *
     * @throws \View_Exception
     */
    public function __construct(string $file)
    {
        $this->setFilename($file);
    }

    /**
     * Sets the view filename.
     *
     *     $view->setFilename($file);
     *
     * @param   string $file view filename
     *
     * @return  View
     * @throws  View_Exception
     */
    private function setFilename(string $file): ViewInterface
    {
        if (($path = Kohana::find_file('views', $file)) === false) {
            throw new View_Exception('The requested view :file could not be found', [
                ':file' => $file,
            ]);
        }

        // Store the file path locally
        $this->file = $path;

        return $this;
    }

    /**
     * Assigns a variable by name. Assigned values will be available as a
     * variable within the view file:
     *
     *     // This value can be accessed as $foo within the view
     *     $view->set('foo', 'my value');
     *
     * You can also use an array to set several values at once:
     *
     *     // Create the values $food and $beverage in the view
     *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
     *
     * @param   string $key   variable name or an array of variables
     * @param   mixed  $value value
     *
     * @return  $this
     */
    public function set(string $key, $value): ViewInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Renders the view object to a string. Global and local data are merged
     * and extracted to create local variables within the view file.
     *
     *     $output = $view->render();
     *
     * @return  string
     * @throws \View_Exception
     */
    public function render(): string
    {
        if (empty($this->file)) {
            throw new View_Exception('You must set the file to use within your view before rendering');
        }

        // Combine local and global data and capture the output
        return self::capture($this->file, $this->data);
    }
}
