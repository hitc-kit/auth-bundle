<?php

namespace HitcKit\AuthBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class DividingLoader extends Loader
{
    private $imported;

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();
        $name = $type.'_'.$resource;
        $route = $this->getRoutes()->get($name);

        if ($route) {
            $routes->add($name, $route);
            $this->getRoutes()->remove($name);
        }

        return $routes;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return 'hitc_kit_auth' === $type && $this->getRoutes()->get($type.'_'.$resource);
    }

    protected function getRoutes(): RouteCollection {
        if (!isset($this->imported)) {
            $controller = '@HitcKitAuthBundle/Controller/SecurityController.php';
            /** @var RouteCollection $imported */
            $this->imported = $this->import($controller, 'annotation');
        }

        return $this->imported;
    }
}
