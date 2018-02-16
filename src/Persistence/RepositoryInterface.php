<?php

namespace Biig\Component\User\Persistence;

/**
 * Interface RepositoryInterface.
 */
interface RepositoryInterface
{
    /**
     * Finds all objects in the repository.
     *
     * @return array the objects
     */
    public function findAll();
}
