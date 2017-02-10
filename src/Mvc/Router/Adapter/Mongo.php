<?php
namespace Vegas\Mvc\Router\Adapter;

use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Router;

/**
 * Class Mongo
 * Default router using standard Phalcon router.
 *
 * @package Vegas\Mvc\Router\Adapter
 */
class Mongo extends Router implements Di\InjectionAwareInterface
{
    /**
     * Standard router constructor
     *
     * @param DiInterface $dependencyInjector
     * @param bool $keepDefaultRoutes
     */
    public function __construct(DiInterface $dependencyInjector, $keepDefaultRoutes = false)
    {
        parent::__construct($keepDefaultRoutes);
        $this->removeExtraSlashes(true);
        $this->setDI($dependencyInjector);
    }

    public function handle($uri = null)
    {
        if (empty($uri)) {
            $uri = $this->getRewriteUri();
        }

        $di = $this->getDI();
        $routeManager = $di->get('databaseRouteManager');
        $routes = $di->get('config')->get('routes', false);

        $route = $routeManager->findByUrl($uri);

        if ($route && $routes) {
            $routeParams = $routes->get($route->name, false);

            if ($routeParams) {
                $this->add($uri, [
                    'module' => $routeParams->module,
                    'controller' => $routeParams->controller,
                    'action' => $routeParams->action,
                    0 => substr($uri, 1)
                ]);
            } else {
                throw new \Exception('Database route not configured');
            }
        }

        return parent::handle($uri);
    }
}
