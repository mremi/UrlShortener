<?php

namespace Mremi\UrlShortener\Model;

/**
 * Link manager interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface LinkManagerInterface
{
    /**
     * Creates and returns a new link instance
     *
     * @return LinkInterface
     */
    public function create();
}
