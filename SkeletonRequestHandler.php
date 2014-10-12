<?php
namespace PhpSkeleton;

/**
 * Base class for all request handlers.
 */
abstract class SkeletonRequestHandler {
	/**
	 * @param SkeletonRequest $request
	 * @return SkeletonResponse
	 */
	abstract public function handle($request);
}
