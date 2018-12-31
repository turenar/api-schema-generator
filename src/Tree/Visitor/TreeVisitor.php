<?php

namespace Turenar\ApiSchema\Tree\Visitor;


use Turenar\ApiSchema\Tree\Endpoint;
use Turenar\ApiSchema\Tree\Input;
use Turenar\ApiSchema\Tree\ObjectCollation;
use Turenar\ApiSchema\Tree\Output;
use Turenar\ApiSchema\Tree\ValueCollation;

interface TreeVisitor
{
	public function visitEndpoint(Endpoint $endpoint);

	public function visitInput(Input $input);

	public function visitObjectCollation(ObjectCollation $collation);

	public function visitOutput(Output $output);

	public function visitValueCollation(ValueCollation $collation);
}
