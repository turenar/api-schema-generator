<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\Exception\UnknownElementException;

abstract class AbstractTreeVisitor implements TreeVisitor
{
	public function visit(TreeElement $element, $extras = null)
	{
		if ($element instanceof Endpoint) {
			return $this->visitEndpoint($element, $extras);
		} elseif ($element instanceof Input) {
			return $this->visitInput($element, $extras);
		} elseif ($element instanceof Output) {
			return $this->visitOutput($element, $extras);
		} elseif ($element instanceof ObjectCollation) {
			return $this->visitObjectCollation($element, $extras);
		} elseif ($element instanceof ValueCollation) {
			return $this->visitValueCollation($element, $extras);
		} else {
			throw UnknownElementException::create($element, $extras);
		}
	}
}
