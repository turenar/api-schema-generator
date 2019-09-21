<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;

use Turenar\ApiSchema\Exception\SpecException;

class ValueCollation extends AbstractTreeElement
{


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
				throw new SpecException($this->spec, $this->spec->getRefPath(), "Unknown spec type: $type");
		}
	}

	public function isArray(): bool
	{
		return $this->spec->getBool('array', false);
	}

	public function isRequired(): bool
	{
		return !$this->spec->hasField('default') && $this->spec->getField('required', true);
	}

	public function hasDefault()
	{
		return $this->spec->hasField('default');
	}

	public function getDefault()
	{
		return $this->spec->getField('default');
	}

	public function getRawType(): string
	{
		$typeField = $this->spec->requireField('type');
		if (!is_string($typeField)) {
			throw new SpecException($this->spec,
				$this->spec->newChildPath('type'), "string expected, but got " . gettype($typeField));
		}
		return $typeField;
	}

	public function isNullable(): bool
	{
		return substr($this->getRawType(), 0, 1) === '?' || $this->spec->getField('nullable', false);
	}

	public function getType(): string
	{
		if ($this->isNullable()) {
			return substr($this->getRawType(), 1);
		} else {
			return $this->getRawType();
		}
	}
}
