
$request = Illuminate\Http\Request::create('/agenda/available-slots', 'GET', ['psicologo_id' => 13]);
$controller = app()->make(\App\Http\Controllers\CitaController::class);
$response = $controller->getAvailableSlots($request);
echo $response->getContent();
