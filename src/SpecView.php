<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


class SpecView implements \IteratorAggregate
{
	/** @var IncludeResolver */
	private $resolver;
	/** @var array */
	private $arr;
	/** @var string */
	private $filename;
	/** @var string */
	private $ref_path;
	/** @var SpecView|null */
	private $included_by;
	/** @var SpecView[] */
	private $included_fields = [];


	public function __construct(
		IncludeResolver $resolver,
		?string $name,
		array $arr,
		string $filename,
		string $ref_path,
		SpecView $parent = null
	) {
		$this->resolver = $resolver;
		$this->filename = $filename;
		$this->ref_path = $ref_path;
		$this->included_by = $parent === null ? null : $parent->included_by;
		foreach ($arr as $field_name => $value) {
			if (is_array($value)) {
				$arr[$field_name] = new SpecView($resolver, $field_name, $value, $filename,
					$this->newChildPath($field_name), $this);
			}
		}
		$this->arr = $arr;
		$this->resolveInclude($name);
	}

	public function getChild(string $name): SpecView
	{
		$child = $this->getField($name);
		if ($child instanceof SpecView) {
			return $child;
		} else {
			throw new SpecException($this, $this->newChildPath($name), 'not array');
		}
	}

	public function hasChild(string $name): bool
	{
		return $this->getField($name) instanceof SpecView;
	}

	public function requireField(string $field_name)
	{
		if (!$this->hasField($field_name)) {
			throw new SpecException($this, $this->newChildPath($field_name), 'not found');
		}
		return $this->getField($field_name);
	}

	public function getField(string $field_name, $default = null)
	{
		return $this->arr[$field_name] ?? $default;
	}

	public function getBool(string $field_name, ?bool $default = null): ?bool
	{
		$value = $this->getField($field_name, $default);
		if ($value !== null && !is_bool($value)) {
			throw new SpecException($this, $this->newChildPath($field_name), 'boolean expected but ' . gettype($value));
		}
		return $value;
	}

	public function hasField($field_name)
	{
		return array_key_exists($field_name, $this->arr);
	}

	/**
	 * @return \Generator
	 */
	public function getIterator()
	{
		foreach ($this->arr as $key => $value) {
			yield $key => $value;
		}
	}

	/**
	 * @return string
	 */
	public function getFilename(): string
	{
		return $this->filename;
	}

	/**
	 * @return string
	 */
	public function getRefPath(): string
	{
		return $this->ref_path;
	}

	/**
	 * @return IncludeResolver
	 */
	public function getResolver(): IncludeResolver
	{
		return $this->resolver;
	}

	public function newChild(string $key, $included_by = '<unknown>'): SpecView
	{
		$this->arr[$key] = [];
		$this->included_fields[$key] = $included_by;
		return $this;
	}
	/**
	 * @param string $key
	 * @return string
	 */
	public function newChildPath($key): string
	{
		if (is_numeric($key)) {
			$new_path = $this->ref_path . '[' . $key . ']';
		} else {
			$new_path = $this->ref_path . '.' . $key;
		}
		return $new_path;
	}

	protected function resolveInclude(?string $name)
	{
		if (isset($this->arr['+include'])) {
			$includes = $this->arr['+include'];
			if (!is_array($includes)) {
				$includes = [$includes];
			}
			foreach ($includes as $include) {
				$resolved_spec = $this->resolver->resolve($this, $name, $include);
				if ($resolved_spec === null) {
					$reason = sprintf('include file not found (%s)', $include);
					throw new SpecException($this, $this->newChildPath('+include'), $reason);
				}
				$resolved_spec->included_by = $this;
				if (!$resolved_spec->hasChild($name)) {
					$reason = sprintf('included file(%s) has no "%s"', $resolved_spec->getFilename(), $name);
					throw new SpecException($this, $this->newChildPath('+include'), $reason);
				}
				$this->merge($resolved_spec->getChild($name));
			}
		}
	}

	protected function getIncludedBy($field = null)
	{
		if ($field !== null && isset($this->included_fields[$field])) {
			return $this->included_fields[$field];
		} else {
			return $this;
		}
	}

	public function merge(SpecView $another)
	{
		foreach ($another->arr as $field_name => $new_value) {
			if ($new_value instanceof SpecView) {
				$current_value = $this->arr[$field_name] ?? null;
				if ($current_value instanceof SpecView) {
					$current_value->merge($new_value);
				} else if ($current_value === null) {
					$this->arr[$field_name] = $new_value;
					$this->included_fields[$field_name] = $another->getIncludedBy($field_name);
				} else {
					// FIXME more friendly message
					throw new SpecException($this, $this->newChildPath($field_name), 'merge failed');
				}
			} else {
				$this->arr[$field_name] = $new_value;
				$this->included_fields[$field_name] = $another->getIncludedBy($field_name);
			}
			unset($this->arr['+include']);
		}
	}
}
