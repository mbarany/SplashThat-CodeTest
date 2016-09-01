<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'   => 'pdo_mysql',
        'host'      => 'db',
        'dbname'    => 'SplashCodeTest',
        'user'      => 'splash',
        'password'  => 'splash',
        'charset'   => 'utf8mb4',
    ],
]);


// Routes

// API
$app->get('/api/events', function () use ($app) {
	$events = $app['db']->fetchAll('SELECT events.*, COUNT(guests.id) guests FROM events LEFT JOIN guests on events.id = guests.event_id group by events.id
');
    return $app->json($events);
});

$app->get('/api/event/{eventId}', function ($eventId) use ($app) {
	$events = $app['db']->fetchAll('SELECT * FROM events WHERE id = ?', [$eventId]);
	if (count($events) !== 1) {
		return $app->abort(404);
	}
	$event = reset($events);
	$event['guests'] = $app['db']->fetchAll('SELECT * FROM guests WHERE event_id = ?', [$eventId]);
    return $app->json($event);
});

$app->post('/api/event', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
	$data = array_filter($request->request->all());
	$results = $app['db']->insert('events', $data);
	if (count($results) !== 1) {
		return $app->abort(404);
	}
	$data['id'] = $app['db']->lastInsertId();
    return $app->json($data);
});

$app->put('/api/event/{eventId}', function (Symfony\Component\HttpFoundation\Request $request, $eventId) use ($app) {
	$data = array_filter($request->request->all());
	unset($data['id']); // Just in case of some basic hacking
	$results = $app['db']->update('events', $data, ['id' => $eventId]);
	if (count($results) !== 1) {
		return $app->abort(404);
	}
	$data['id'] = $eventId;
    return $app->json($data);
});

$app->post('/api/event/{eventId}/guest', function (Symfony\Component\HttpFoundation\Request $request, $eventId) use ($app) {
	$data = array_filter($request->request->all());
	$data['event_id'] = $eventId;
	$results = $app['db']->insert('guests', $data);
	if (count($results) !== 1) {
		return $app->abort(404);
	}
	$data['id'] = $app['db']->lastInsertId();
    return $app->json($data);
});

// Wildcard route for SPA
$app->get('{url}', function () {
    return file_get_contents(__DIR__ . '/../layouts/main.html');
})->assert('url', '.*');

$app->run();
