<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Generator\Schema;


use Turenar\ApiSchema\Exception\SpecException;
use Turenar\ApiSchema\SpecView;
use Turenar\ApiSchema\Tree\AbstractTreeVisitor;
use Turenar\ApiSchema\Tree\Endpoint;
use Turenar\ApiSchema\Tree\Input;
use Turenar\ApiSchema\Tree\ObjectCollation;
use Turenar\ApiSchema\Tree\Output;
use Turenar\ApiSchema\Tree\ValueCollation;

class SchemaVisitor extends AbstractTreeVisitor
{

	public function visitEndpoint(Endpoint $endpoint, $extras)
	{
		return [
			'input' => $this->visitInput($endpoint->getInput(), null),
			'output' => $this->visitOutput($endpoint->getOutput(), null)
		];
	}

	public function visitSchemaRootBase(ObjectCollation $collation)
	{
		$schema = $this->visitObjectCollation($collation, null);
		$schema += [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
		];
		return $schema;
	}

	public function visitInput(Input $input, $extras)
	{
		return $this->visitSchemaRootBase($input);
	}

	public function visitOutput(Output $output, $extras)
	{
		return $this->visitSchemaRootBase($output);
	}

	public function visitObjectCollation(ObjectCollation $parent, $extras)
	{
		$schema = [
			'type' => 'object',
			'required' => [],
			'properties' => [],
			'additionalProperties' => false,
		];
		foreach ($parent->getCollations() as $name => $collation) {
			if ($collation->isRequired()) {
				$schema['required'][] = $name;
			}
			$schema['properties'][$name] = $this->visit($collation);
		}

		if ($parent->isArray()) {
			$schema = [
				'type' => 'array',
				'items' => $schema
			];
		}
		if ($parent->isNullable()) {
			$schema['type'] = [$schema['type'], 'null'];
		}
		return $schema;
	}

	/**
	 * @param SpecView $spec
	 * @param $type
	 * @return array
	 * @throws SpecException
	 */
	protected function generateBaseType(SpecView $spec, $type): array
	{
		switch ($type) {
			case 'string':
				return ['type' => 'string'];
			case 'int':
			case 'integer':
				return ['type' => 'integer'];
			case 'number':
				return ['type' => 'number'];
			case 'boolean':
				return ['type' => 'boolean'];
			case 'date':
				return ['type' => 'string', 'format' => 'date']; // date may be not defined in schema specification
			case 'datetime':
				return ['type' => 'string', 'format' => 'date-time'];
			default:
				throw new SpecException($spec, $spec->getRefPath(), "Unknown spec type: $type");
		}
	}

	protected function generateFieldSchema(SpecView $spec, ValueCollation $collation)
	{
		$schema = $this->generateBaseType($spec, $collation->getType());
		if ($collation->hasEnum()) {
			$schema['enum'] = $collation->getEnum()->toArray();
		}
		if ($collation->isNullable()) {
			$schema['type'] = [$schema['type'], 'null'];
			if ($collation->hasEnum()) {
				$schema['type'][] = null;
			}
		}
		return $schema;
	}

	public function visitValueCollation(ValueCollation $collation, $extras)
	{
		$schema = $this->generateFieldSchema($collation->getSpec(), $collation);
		if ($collation->hasDefault()) {
			$schema['default'] = $collation->getDefault();
		}
		if ($collation->isArray()) {
			$schema = [
				'type' => 'array',
				'items' => $schema,
			];
		}
		return $schema;
	}
}
