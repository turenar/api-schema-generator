<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


interface TreeVisitor
{
	public function visitEndpoint(Endpoint $endpoint, $extras);

	public function visitInput(Input $input, $extras);

	public function visitObjectCollation(ObjectCollation $collation, $extras);

	public function visitOutput(Output $output, $extras);

	public function visitValueCollation(ValueCollation $collation, $extras);
}
