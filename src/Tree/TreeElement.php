<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\SpecView;

interface TreeElement
{
	public function isRequired(): bool;

	public function getSpec(): SpecView;
}
