<?php

$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$staticFile = __DIR__ . $uriPath;
if ($uriPath !== '/' && is_file($staticFile)) {
    return false;
}

require_once __DIR__ . '/app/autoload.php';

use App\Core\Router;

$router = new Router();
$GLOBALS['router'] = $router;

$router->get('/', 'HomeController@info');
$router->get('/info', 'HomeController@info');
$router->get('/registro', 'LawyerController@register');
$router->get('/manual', 'HomeController@manual');
$router->get('/solicitudes', 'RequestController@form');
$router->get('/reportes', 'ReportController@index');
$router->get('/crm', 'CrmController@index');

$router->get('/login', 'AuthController@loginForm');
$router->post('/api/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->post('/api/registro-abogado', 'LawyerController@apiRegister');
$router->post('/api/registro-afectado', 'RequestController@apiRegister');
$router->post('/api/asignar-caso', 'CrmController@apiAssign');
$router->post('/api/cerrar-caso', 'CrmController@apiClose');
$router->post('/api/reabrir-caso', 'CrmController@apiReopen');
$router->post('/api/actualizar-caso', 'CrmController@apiUpdate');
$router->post('/api/eliminar-caso', 'CrmController@apiDelete');
$router->get('/api/obtener-caso', 'CrmController@apiGet');
$router->get('/api/obtener-abogados', 'LawyerController@apiList');
$router->get('/api/obtener-personas', 'RequestController@apiList');
$router->get('/api/obtener-casos', 'CrmController@apiList');
$router->get('/api/estadisticas', 'CrmController@apiStats');
$router->get('/api/buscar-abogados', 'LawyerController@apiSearch');
$router->post('/api/actualizar-abogado', 'LawyerController@apiUpdate');
$router->post('/api/eliminar-abogado', 'LawyerController@apiDelete');
$router->get('/api/buscar-personas', 'RequestController@apiSearch');
$router->get('/api/exportar-abogados', 'LawyerController@apiExport');
$router->get('/api/exportar-casos', 'CrmController@apiExport');
$router->post('/api/cambio-estado', 'CrmController@apiChangeStatus');
$router->post('/api/agregar-comentario', 'CrmController@apiAddComment');
$router->get('/api/actividades', 'CrmController@apiActivities');
$router->post('/api/cambiar-password', 'AuthController@apiChangePassword');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
