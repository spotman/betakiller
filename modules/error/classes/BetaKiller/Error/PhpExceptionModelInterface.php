<?php
namespace BetaKiller\Error;

use BetaKiller\Helper\HasAdminUrlInterface;
use BetaKiller\Model\UserInterface;

interface PhpExceptionModelInterface extends HasAdminUrlInterface
{
    const STATE_NEW         = 'new';
    const STATE_RESOLVED    = 'resolved';
    const STATE_REPEATED    = 'repeated';
    const STATE_IGNORED     = 'ignored';

    /**
     * @return string
     */
    public function getID();

    /**
     * @return string
     */
    public function getHash();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setHash($value);

    /**
     * @param string $hash
     *
     * @return PhpExceptionModelInterface|null
     */
    public function findByHash($hash);

    /**
     * @param string $module
     *
     * @return $this
     */
    public function addModule($module);

    /**
     * @return string[]
     */
    public function getModules();

    /**
     * @return int
     */
    public function getCounter();

    /**
     * @return $this
     */
    public function incrementCounter();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage($value);

    /**
     * @param string $path
     *
     * @return $this
     */
    public function addPath($path);

    /**
     * @return string[]
     */
    public function getPaths();

    /**
     * @param string $url
     *
     * @return $this
     */
    public function addUrl($url);

    /**
     * @return string[]
     */
    public function getUrls();

    /**
     * @param \DateTime|NULL $time
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $time);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Unix timestamp of last notification time
     * 
     * @param \DateTime|NULL $time
     * 
     * @return $this
     */
    public function setLastSeenAt(\DateTime $time);

    /**
     * Unix timestamp of last notification time
     * 
     * @return \DateTime|NULL
     */
    public function getLastSeenAt();

    /**
     * Unix timestamp of last notification time
     *
     * @param \DateTime|NULL $time
     *
     * @return $this
     */
    public function setLastNotifiedAt(\DateTime $time);

    /**
     * Unix timestamp of last notification time
     *
     * @return \DateTime|NULL
     */
    public function getLastNotifiedAt();

    /**
     * Mark exception as new (these exceptions require developer attention)
     *
     * @param UserInterface $user
     * @return $this
     */
    public function markAsNew(UserInterface $user);

    /**
     * Mark exception as repeated (it was resolved earlier but repeated now)
     *
     * @param UserInterface $user
     * @return $this
     */
    public function markAsRepeated(UserInterface $user);

    /**
     * Mark exception as resolved
     * 
     * @param UserInterface $user
     * @return $this
     */
    public function markAsResolvedBy(UserInterface $user);

    /**
     * Mark exception as ignored
     *
     * @param UserInterface $user
     * @return $this
     */
    public function markAsIgnoredBy(UserInterface $user);

    /**
     * Returns TRUE if current exception is in 'new' state
     *
     * @return bool
     */
    public function isNew();

    /**
     * Returns TRUE if exception was resolved
     * 
     * @return bool
     */
    public function isResolved();

    /**
     * Returns TRUE if current exception is in 'repeat' state
     *
     * @return bool
     */
    public function isRepeated();

    /**
     * Returns TRUE if current exception is in 'ignored' state
     *
     * @return bool
     */
    public function isIgnored();

    /**
     * Returns user which had resolved this exception
     * 
     * @return UserInterface
     */
    public function getResolvedBy();

    /**
     * @return PhpExceptionHistoryModelInterface[]
     */
    public function getHistoricalRecords();

    public function save();

    public function delete();
}
