<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task
{
    use BetaKiller\Helper\Base;

    const RED           = Minion_CLI::RED;
    const GREEN         = Minion_CLI::GREEN;
    const BLUE          = Minion_CLI::BLUE;

    const LIGHT_BLUE    = Minion_CLI::LIGHT_BLUE;

    /**
     * Execute the task with the specified set of options
     *
     * @return null
     */
    public function execute()
    {
        $log_level = ( isset($this->_options['debug']) )
            ? Log::DEBUG
            : $this->get_max_log_level();

        Log::instance()->attach(new Minion_Log(), $log_level);

        // Auth for CLI
        $user = $this->get_cli_user_model();
        Auth::instance()->force_login($user);

        parent::execute();
    }

    protected function get_cli_user_model()
    {
        $username = 'minion';

        /** @var Model_User $orm */
        $orm = \ORM::factory('User');

        $user = $orm->search_by($username);

        if (!$user->loaded())
        {
            $password = microtime();

            $host = parse_url(Kohana::$base_url, PHP_URL_HOST);
            $email = $username.'@'.$host;

            /** @var Model_User $user */
            $user = $orm
                ->set_username($username)
                ->set_password($password)
                ->set_email($email)
                ->create();

            // Allowing everything (admin may remove some roles later if needed)
            $user->add_all_available_roles();
        }

        return $user;
    }

    /**
     *
     * Constant like Log::INFO
     * @return int
     */
    protected function get_max_log_level()
    {
        return Log::INFO;
    }

    /**
     * @param $text
     * @param null $color
     * @return $this
     */
    protected function write($text, $color = NULL)
    {
        if ($color)
            $text = $this->colorize($text, $color);

        Minion_CLI::write($text);

        return $this;
    }

    /**
     * @param $text
     * @param bool $eol
     * @param string $color
     * @return $this
     */
    protected function write_replace($text, $eol = FALSE, $color = NULL)
    {
        if ($color)
            $text = $this->colorize($text, $color);

        Minion_CLI::write_replace($text, $eol);

        return $this;
    }

    private function colorize($text, $fore, $back = NULL)
    {
        return Minion_CLI::color($text, $fore, $back);
    }

    /**
     * Get user input from CLI
     *
     * @param string $message
     * @param array $options
     * @return string
     */
    protected function read($message, array $options = NULL)
    {
        return Minion_CLI::read($message, $options);
    }

    /**
     * Get password user input from CLI
     *
     * @param $message
     * @return string
     */
    protected function password($message)
    {
        return Minion_CLI::password($message);
    }
}
