<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$routes = collect(\Route::getRoutes())->filter(function($r) {
    return str_starts_with($r->uri(), "api/v1") && 
           !str_starts_with($r->uri(), "api/v1/health") && 
           !str_starts_with($r->uri(), "api/v1/login") && 
           !str_starts_with($r->uri(), "api/v1/docs") && 
           !str_starts_with($r->uri(), "api/v1/user");
});
$laravelPaths = [];
foreach($routes as $r) {
    foreach(array_diff($r->methods(), ["HEAD"]) as $m) {
        $laravelPaths[] = strtolower($m) . " /" . $r->uri();
    }
}

$swaggerPathsJson = json_decode(file_get_contents(storage_path("api-docs/api-docs.json")), true)["paths"] ?? [];
$swaggerPaths = [];
foreach ($swaggerPathsJson as $path => $methods) {
    foreach ($methods as $method => $data) {
        $swaggerPaths[] = strtolower($method) . " " . $path;
    }
}

$missingInSwagger = array_diff($laravelPaths, $swaggerPaths);
$extraInSwagger = array_diff($swaggerPaths, $laravelPaths);

echo "Missing in Swagger:\n";
print_r($missingInSwagger);
echo "\nExtra in Swagger:\n";
print_r($extraInSwagger);

