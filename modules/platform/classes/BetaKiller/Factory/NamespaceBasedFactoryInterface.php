<?php
namespace BetaKiller\Factory;

interface NamespaceBasedFactoryInterface
{
    public function setExpectedInterface($interfaceName): NamespaceBasedFactoryInterface;

    /**
     * @param string ...$namespaces
     *
     * @return NamespaceBasedFactoryInterface
     */
    public function setClassNamespaces(string ...$namespaces): NamespaceBasedFactoryInterface;

    /**
     * @param string $suffix
     *
     * @return NamespaceBasedFactoryInterface
     */
    public function setClassSuffix(string $suffix): NamespaceBasedFactoryInterface;

    /**
     * @param string $ns
     *
     * @return NamespaceBasedFactoryInterface
     */
    public function addRootNamespace(string $ns): NamespaceBasedFactoryInterface;

    /**
     * @return NamespaceBasedFactoryInterface
     */
    public function cacheInstances(): NamespaceBasedFactoryInterface;

    /**
     * @param callable $func
     *
     * @return NamespaceBasedFactoryInterface
     */
    public function prepareArgumentsWith(callable $func): NamespaceBasedFactoryInterface;

    /**
     * @return NamespaceBasedFactoryInterface
     */
    public function rawInstances(): NamespaceBasedFactoryInterface;

    /**
     * @param callable(string $className, array $arguments): mixed $fn
     *
     * @return \BetaKiller\Factory\NamespaceBasedFactoryInterface
     */
    public function withInstanceFactory(callable $fn): NamespaceBasedFactoryInterface;

    /**
     * @return NamespaceBasedFactoryInterface
     */
    public function useInterface(): NamespaceBasedFactoryInterface;

    /**
     * @return NamespaceBasedFactoryInterface
     */
    public function legacyNaming(): NamespaceBasedFactoryInterface;

    /**
     * @param string     $codename
     * @param array|null $arguments
     *
     * @return mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create(string $codename, array $arguments = null);
}
