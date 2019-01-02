<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Resolver;


use Turenar\ApiSchema\SpecView;

interface IncludeResolver
{
	/**
	 * @param SpecView $parent
	 * @param string $name
	 * @param string $include_filename filename
	 * @return null|SpecView
	 */
	public function resolve(SpecView $parent, string $name, string $include_filename): ?SpecView;
}
