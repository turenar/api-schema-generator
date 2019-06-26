<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


use Turenar\ApiSchema\Exception\FileNotFoundException;
use Turenar\ApiSchema\Exception\NotSupportedConversionException;
use Turenar\ApiSchema\Generator\ContentGenerator;

class SourceTargetIterable implements \IteratorAggregate
{
	/** @var ContentGenerator */
	private $generator;
	/** @var string */
	private $source;
	/** @var string */
	private $target;
	/** @var \Generator */
	private $iterator;
	/** @var bool */
	private $is_target_single;

	public function __construct(ContentGenerator $generator, string $source, string $target)
	{
		$this->generator = $generator;
		$this->source = $source;
		$this->target = $target;

		$this->createIterator();
	}

	public function getIterator(): \Generator
	{
		return $this->iterator;
	}

	/**
	 * @return bool
	 */
	public function isTargetSingle(): bool
	{
		return $this->is_target_single;
	}

	/**
	 * @throws NotSupportedConversionException
	 * @throws FileNotFoundException
	 */
	public function createIterator()
	{
		$source_info = new \SplFileInfo($this->source);
		if ($source_info->isDir()) {
			$this->createDirIterator();
		} elseif ($source_info->isFile()) {
			$this->createFileIterator();
		} else {
			throw FileNotFoundException::create($this->source);
		}
	}

	/**
	 * @throws NotSupportedConversionException
	 */
	public function createDirIterator()
	{
		$target_info = new \SplFileInfo($this->target);

		$file_pattern = /** @lang RegExp */
			'@^' . preg_quote($this->source) . '/?(?:((?:[^/_.][^/]+/)*)([^_./][^/]+\.yaml))$@';
		$dir = new \RecursiveDirectoryIterator($this->source, \RecursiveDirectoryIterator::CURRENT_AS_PATHNAME);
		$ite = new \RecursiveIteratorIterator($dir);
		$yaml_files_iterator = new \RegexIterator($ite, $file_pattern, \RegexIterator::GET_MATCH);
		if (!file_exists($this->target)) {
			// not exists
			$is_target_single = !$this->generator->canTargetMultiFile();
		} else {
			$is_target_single = !$target_info->isDir();
		}
		if ($is_target_single) {
			$this->iterator = $this->iterateDirToFile($yaml_files_iterator);
		} else {
			$this->iterator = $this->iterateDirToDir($yaml_files_iterator);
		}
		$this->is_target_single = $is_target_single;
	}

	/**
	 * @param \RegexIterator $iterator
	 * @return \Generator
	 * @throws NotSupportedConversionException
	 */
	protected function iterateDirToDir(\RegexIterator $iterator): \Generator
	{
		if (!$this->generator->canTargetMultiFile()) {
			throw NotSupportedConversionException::notSupportedToDir(
				$this->generator, $this->target);
		}
		$this->is_target_single = false;

		foreach ($iterator as $file_match) {
			$source = new FilePath($this->source, $file_match[1], $file_match[2]);
			$target = new FilePath($this->target, $file_match[1],
				$this->replaceExtension($file_match[2], $this->generator->targetExtension()));

			yield $source => $target;
		}
	}

	/**
	 * @param \RegexIterator $iterator
	 * @return \Generator
	 * @throws NotSupportedConversionException
	 */
	protected function iterateDirToFile(\RegexIterator $iterator): \Generator
	{
		if (!$this->generator->canTargetSingleFile()) {
			throw NotSupportedConversionException::notSupportedDirToFile(
				$this->generator, $this->source, $this->target);
		}
		$this->is_target_single = true;

		$target_file = new FilePath($this->target);

		foreach ($iterator as $file_match) {
			$source = new FilePath($this->source, $file_match[1], $file_match[2]);
			yield $source => $target_file;
		}
	}

	protected function replaceExtension(string $source, string $target_extension)
	{
		$source_filename = basename($source);
		$source_extension_index = strrpos($source_filename, '.');
		$target_filename = $source_extension_index < 0
			? $source_filename
			: (substr($source_filename, 0, $source_extension_index) . '.' . $target_extension);
		return dirname($source) . '/' . $target_filename;
	}

	protected function createFileIterator()
	{
		$target = $this->target;
		$target_info = new \SplFileInfo($target);
		if ($target_info->isDir()) {
			$target = $target . basename($this->source);
		}
		$this->iterator = $this->iterateFile($target);
	}

	protected function iterateFile(string $target): \Generator
	{
		$this->is_target_single = true;
		yield $this->source => $target;
	}
}
