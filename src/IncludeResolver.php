<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


interface IncludeResolver
{
	/**
	 * @param string $include_filename filename
	 * @return string|null
	 */
	public function resolve(string $include_filename);
}
