<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree\Visitor;


use Turenar\ApiSchema\Tree\TreeElement;

class UnknownElementException extends \LogicException
{
	public static function create(TreeElement $element)
	{
		return new static(sprintf("%s: not supported in visitor", get_class($element)));
	}
}
