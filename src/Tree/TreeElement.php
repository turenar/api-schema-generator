<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


interface TreeElement
{
	public function getSchema();

	public function isRequired();
}
