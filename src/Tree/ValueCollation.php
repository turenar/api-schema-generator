<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\SpecView;

class ValueCollation implements TreeElement
{
	/** @var SpecView */
	private $spec;

	public function __construct(SpecView $spec)
	{
		$this->spec = $spec;
	}

	public function generateFieldSchema($type)
	{
		$nullable = substr($type, -1) === '?';
		if ($nullable) {
			$type = substr($type, 0, -1);
		}
		$schema = $this->generateBaseType($type);
		if ($nullable) {
			$schema['type'] = [$schema['type'], 'null'];
		}
		return $schema;
	}

	/**
	 * @param $type
	 * @return array
	 * @throws \Exception
	 */
	protected function generateBaseType($type): array
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
				throw new \Exception("Unknown spec type: $type");
		}
	}
	public function getSchema()
	{
		$schema = $this->generateFieldSchema($this->spec->requireField('type'));
		if ($this->spec->hasField('default')) {
			$schema['default'] = $this->spec->getField('default');
		}
		if ($this->spec->getField('array', false)) {
			$schema = [
				'type' => 'array',
				'items' => $schema,
			];
		}
		return $schema;
	}

	public function isRequired()
	{
		return !$this->spec->hasField('default') && $this->spec->getField('required', true);
	}
}
