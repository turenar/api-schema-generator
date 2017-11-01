<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\SpecView;

abstract class SchemaRootBase extends ObjectCollation
{
	public function __construct(SpecView $spec)
	{
		parent::__construct($spec, true);
	}

	public function getSchema()
	{
		$schema = parent::getSchema();
		$schema += [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
		];

		return $schema;
	}
}
