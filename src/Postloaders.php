<?php

namespace My\Postloaders;

final class Postloaders
{
    private static $instance = null;

    public static function getInstance()
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $loaders = [];

    private function __construct()
    {
    }

    public function get($loader_id)
    {
        if (isset($this->loaders[$loader_id])) {
            return $this->loaders[$loader_id];
        }

        return null;
    }

    public function register($loader)
    {
        if (! is_a($loader, 'Postloader')) {
            $loader = new Postloader($loader);
        }

        $this->loaders[$loader->getID()] = $loader;
    }

    public function unregister($loader_id)
    {
        unset($this->loaders[$loader_id]);
    }

    public function render($loader_id)
    {
        $loader = $this->get($loader_id);

        if ($loader) {
            $loader->render();
        }
    }
}
