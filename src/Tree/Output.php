<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


class Output extends Input
{
	public function __construct($spec)
	{
		parent::__construct($spec);
	}

	public function getSchema()
	{
		$schema = parent::getSchema();
		$schema['properties']['meta'] = ['type' => 'object'];
		$schema['required'][] = 'meta';
		return $schema;
	}
}
