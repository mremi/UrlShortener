<?php

namespace Mremi\UrlShortener\Model;

/**
 * Link manager class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class LinkManager implements LinkManagerInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param string $class The Link class namespace
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->class;
    }
}
