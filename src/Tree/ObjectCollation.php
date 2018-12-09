<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\SpecException;
use Turenar\ApiSchema\SpecView;

class ObjectCollation implements TreeElement
{
	/** @var bool */
	protected $required;
	/** @var TreeElement[] */
	protected $collations;
	/** @var SpecView */
	protected $spec;

	/**
	 * ObjectCollation constructor.
	 * @param SpecView $spec
	 * @param bool $root
	 * @throws SpecException
	 */
	public function __construct(SpecView $spec, bool $root = false)
	{
		/** @var TreeElement[] $collations */
		$collations = [];

		if ($root) {
			$fields = $spec;
		} else {
			$this->required = $spec->getField('required', true);

			// FIXME
			/*if (isset($spec['+db_fields'])) {
				foreach ($spec['+db_fields'] as $field_name) {
					$sub_properties[$field_name] = generate_db_column_schema($name, $field_name, $sub_required);
				}
			}*/
			if ($spec->hasField('+fields')) {
				$fields = $spec->getChild('+fields');
			}
		}
		if (isset($fields)) {
			foreach ($fields as $field_name => $field_spec) {
				$collations[$field_name] = self::createCollation($field_spec);
			}
		}

		$this->collations = $collations;
		$this->spec = $spec;
	}

	/**
	 * @param SpecView $spec
	 * @return TreeElement
	 * @throws SpecException
	 */
	protected static function createCollation(SpecView $spec): TreeElement
	{
		if ($spec->hasField('+fields') || $spec->hasField('+db_fields')) {
			return new ObjectCollation($spec);
		} else if ($spec->hasField('type')) {
			return new ValueCollation($spec);
		} else {
			throw new SpecException($spec, $spec->getRefPath(), 'must have field/type');
		}
	}

	public function getSchema()
	{
		$schema = [
			'type' => 'object',
			'required' => [],
			'properties' => [],
			'additionalProperties' => false,
		];
		foreach ($this->collations as $name => $collation) {
			if ($collation->isRequired()) {
				$schema['required'][] = $name;
			}
			$schema['properties'][$name] = $collation->getSchema();
		}

		if ($this->spec->getField('array', false)) {
			$schema = [
				'type' => 'array',
				'items' => $schema
			];
		}
		return $schema;
	}

	public function isRequired()
	{
		return $this->required;
	}

	protected function searchIncludeFile($include_file)
	{
		$include_file = SPEC_DIR . '/_includes/' . $include_file;
		if (file_exists($include_file)) {
			return $include_file;
		} else {
			throw new \Exception("$include_file is not found");
		}
	}
}
