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

	public function __construct(IncludeResolver $resolver, array $arr, string $filename, string $ref_path)
	{
		$this->resolver = $resolver;
		$this->arr = $arr;
		$this->filename = $filename;
		$this->ref_path = $ref_path;
	}

	public function getChild($name): SpecView
	{
		$child = $this->getField($name);
		if (!is_array($child)) {
			throw new SpecException($this, $this->newChildPath($name), 'not array');
		}

		return $this->newSpecView($name, $child);
	}

	public function requireField($field_name)
	{
		if (!$this->hasField($field_name)) {
			throw new SpecException($this, $this->newChildPath($field_name), 'not found');
		}
		return $this->getField($field_name);
	}

	public function getField($field_name, $default = null)
	{
		return $this->arr[$field_name] ?? $default;
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
			if (is_array($value)) {
				yield $key => $this->newSpecView($key, $value);
			} else {
				yield $key => $value;
			}
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

	/**
	 * @param string $key
	 * @return string
	 */
	protected function newChildPath($key): string
	{
		if (is_numeric($key)) {
			$new_path = $this->ref_path . '[' . $key . ']';
		} else {
			$new_path = $this->ref_path . '.' . $key;
		}
		return $new_path;
	}

	protected function newSpecView($name, $child): SpecView
	{
		return new SpecView($this->resolver, $this->resolveInclude($child, $name), $this->filename,
			$this->newChildPath($name));
	}

	protected function resolveInclude(array $arr, string $name)
	{
		if (isset($arr['+include'])) {
			$includes = $arr['+include'];
			if (!is_array($includes)) {
				$includes = [$includes];
			}
			foreach ($includes as $include) {
				$inclusion_file = $this->resolver->resolve($include);
				if ($inclusion_file === null) {
					$reason = sprintf('include file not found (%s)', $include);
					throw new SpecException($this, $this->newChildPath('+include'), $reason);
				}
				$yaml = yaml_parse_file($inclusion_file);
				if ($yaml === false) {
					throw new SpecException($this, $this->newChildPath('+include'), 'parse failed');
				}
				if (!array_key_exists($name, $yaml)) {
					$reason = sprintf('included file(%s) has no "%s"', $inclusion_file, $name);
					throw new SpecException($this, $this->newChildPath('+include'), $reason);
				}
				$arr = array_replace_recursive($arr, $yaml[$name]);
			}
			unset($arr['+include']);
		}
		return $arr;
	}
}
