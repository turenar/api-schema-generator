<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\Exception\UnknownElementException;

abstract class AbstractTreeVisitor implements TreeVisitor
{
	public function visit(TreeElement $element)
	{
		if ($element instanceof Endpoint) {
			return $this->visitEndpoint($element);
		} elseif ($element instanceof Input) {
			return $this->visitInput($element);
		} elseif ($element instanceof Output) {
			return $this->visitOutput($element);
		} elseif ($element instanceof ObjectCollation) {
			return $this->visitObjectCollation($element);
		} elseif ($element instanceof ValueCollation) {
			return $this->visitValueCollation($element);
		} else {
			throw UnknownElementException::create($element);
		}
	}
}
