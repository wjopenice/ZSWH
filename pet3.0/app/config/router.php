<?php
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group as RouterGroup;

$router = new Router();
$router->add('/blog',['namespace'=> 'Backend\Controllers','controller' => 'blog','action'=> 'index']);
$router->add('/blog',['controller' => 'blog','action'=> 'index']);
$router->add('/blog',['module'=> 'index','controller' => 'blog','action'=> 'index']);
$router->add('/blog/{id}',['module'=> 'index','controller' => 'blog','action'=> 'index','params'=>1]);
$router->add('/blog/:controller/:action/:params',['controller'=> 'index','action'=> 'index','params'=>1]);
$router->add("/posts/{year}/{title}", "Posts::show")->setName("show-posts");
$router->add(
    '/news/([0-9]{4})/([0-9]{2})/([0-9]{2})/:params',
    [
        'controller' => 'posts',
        'action'     => 'show',
        'year'       => 1, // ([0-9]{4})
        'month'      => 2, // ([0-9]{2})
        'day'        => 3, // ([0-9]{2})
        'params'     => 4, // :params
    ]
);
//Inside the controller, those named parameters can be accessed as follows
// Get 'year' parameter
$year = $this->dispatcher->getParam('year');
// Get 'month' parameter
$month = $this->dispatcher->getParam('month');
// Get 'day' parameter
$day = $this->dispatcher->getParam('day');
$router->handle();


//Groups of Routes
// Create a group with a common module and controller
$blog = new RouterGroup(['module'=> 'blog', 'controller' => 'index',]);
// All the routes start with /blog
$blog->setPrefix('/blog');
// Add a route to the group
$blog->add('/save',[ 'action' => 'save']);
// Add another route to the group
$blog->add('/edit/{id}',['action' => 'edit']);
// This route maps to a controller different than the default
$blog->add('/blog',['controller' => 'blog','action' => 'index']);
// Add the group to the router
$router->mount($blog);

// Create the router without default routes
$router = new Router(false);

// Set 404 paths
$router->notFound(['controller' => 'index', 'action' => 'route404']);


//Setting default paths
// Setting a specific default
$router->setDefaultModule('backend');
$router->setDefaultNamespace('Backend\Controllers');
$router->setDefaultController('index');
$router->setDefaultAction('index');
// Using an array
$router->setDefaults(['controller' => 'index', 'action'=> 'index']);