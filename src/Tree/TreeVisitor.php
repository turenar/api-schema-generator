<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


interface TreeVisitor
{
	public function visitEndpoint(Endpoint $endpoint);

	public function visitInput(Input $input);

	public function visitObjectCollation(ObjectCollation $collation);

	public function visitOutput(Output $output);

	public function visitValueCollation(ValueCollation $collation);
}
