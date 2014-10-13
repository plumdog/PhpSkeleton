<?php
namespace PhpSkeleton;

class SkeletonHandler {
	public static function handle($routes) {
		// this should include an array called $config.
		require_once "config.php";
		if(isset($config) and is_array($config)) {
			$APP_CONFIG = $config;
		} else {
			throw new \Exception("No config found.");
		}

		$request = new SkeletonRequest($APP_CONFIG);
		$path = (string) $request->path();

		if(array_key_exists($path, $routes)) {
			$handler_name = $routes[$path];
			$handler = new $handler_name($request);
		}

		if(!isset($handler)) {
			$response = SkeletonResponse::error404($request, 'Not Found');
		} else {
			$response = $handler->handle($request);
		}

		$out = (string) $response;

		foreach($response->headers() as $header) {
			header($header);
		}

		echo $out;
	}
}
