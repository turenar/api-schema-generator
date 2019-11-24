<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\Exception\SpecException;
use Turenar\ApiSchema\SpecView;

class ObjectCollation extends AbstractTreeElement
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
		parent::__construct($spec);

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

	public function isRequired(): bool
	{
		return $this->required;
	}

	/**
	 * @return TreeElement[]
	 */
	public function getCollations(): array
	{
		return $this->collations;
	}

	/**
	 * @return SpecView
	 */
	public function getSpec(): SpecView
	{
		return $this->spec;
	}

	public function isArray(): bool
	{
		return $this->spec->getBool('array', false);
	}

	public function isNullable(): bool
	{
		return (bool)$this->spec->getField('nullable', false);
	}
}
