<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Generator\Schema;


use Turenar\ApiSchema\SpecProcessor;
use Turenar\ApiSchema\Tree\Endpoint;

class ExposedSpecProcessor extends SpecProcessor
{
	public function parseFile(string $infile): Endpoint
	{
		return parent::parseFile($infile);
	}
}
