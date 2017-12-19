<?php
namespace BetaKiller\View;


interface ViewInterface
{
    /**
     * @param null       $file
     * @param array|null $data
     *
     * @return \BetaKiller\View\ViewInterface
     */
    public static function factory($file = null, array $data = null);

    /**
     * Sets a global variable, similar to [View::set], except that the
     * variable will be accessible to all views.
     *
     *     View::set_global($name, $value);
     *
     * @param   string $key   variable name or an array of variables
     * @param   mixed  $value value
     *
     * @return  void
     */
    public static function set_global($key, $value = null);

    /**
     * Assigns a global variable by reference, similar to [View::bind], except
     * that the variable will be accessible to all views.
     *
     *     View::bind_global($key, $value);
     *
     * @param   string $key   variable name
     * @param   mixed  $value referenced variable
     *
     * @return  void
     */
    public static function bind_global($key, & $value);

    /**
     * Sets the initial view filename and local data. Views should almost
     * always only be created using [View::factory].
     *
     *     $view = new View($file);
     *
     * @param   string $file view filename
     * @param   array  $data array of values
     *
     * @return  void
     * @uses    View::set_filename
     */
    public function __construct($file = null, array $data = null);

    /**
     * Magic method, searches for the given variable and returns its value.
     * Local variables will be returned before global variables.
     *
     *     $value = $view->foo;
     *
     * [!!] If the variable has not yet been set, an exception will be thrown.
     *
     * @param   string $key variable name
     *
     * @return  mixed
     * @throws  \Kohana_Exception
     */
    public function & __get($key);

    /**
     * Magic method, calls [View::set] with the same parameters.
     *
     *     $view->foo = 'something';
     *
     * @param   string $key   variable name
     * @param   mixed  $value value
     *
     * @return  void
     */
    public function __set($key, $value);

    /**
     * Magic method, determines if a variable is set.
     *
     *     isset($view->foo);
     *
     * [!!] `NULL` variables are not considered to be set by [isset](http://php.net/isset).
     *
     * @param   string $key variable name
     *
     * @return  boolean
     */
    public function __isset($key);

    /**
     * Magic method, unsets a given variable.
     *
     *     unset($view->foo);
     *
     * @param   string $key variable name
     *
     * @return  void
     */
    public function __unset($key);

    /**
     * Magic method, returns the output of [View::render].
     *
     * @return  string
     * @uses    View::render
     */
    public function __toString();

    /**
     * Sets the view filename.
     *
     *     $view->set_filename($file);
     *
     * @param   string $file view filename
     *
     * @return  \BetaKiller\View\ViewInterface
     * @throws  \View_Exception
     */
    public function set_filename($file);

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
     * @return  \BetaKiller\View\ViewInterface
     */
    public function set($key, $value = null);

    /**
     * Assigns a value by reference. The benefit of binding is that values can
     * be altered without re-setting them. It is also possible to bind variables
     * before they have values. Assigned values will be available as a
     * variable within the view file:
     *
     *     // This reference can be accessed as $ref within the view
     *     $view->bind('ref', $bar);
     *
     * @param   string $key   variable name
     * @param   mixed  $value referenced variable
     *
     * @return  $this
     */
    public function bind($key, & $value);

    /**
     * Renders the view object to a string. Global and local data are merged
     * and extracted to create local variables within the view file.
     *
     *     $output = $view->render();
     *
     * [!!] Global variables with the same key name as local variables will be
     * overwritten by the local variable.
     *
     * @param   string $file view filename
     *
     * @return  string
     * @throws  \View_Exception
     * @uses    View::capture
     */
    public function render($file = null);
}
