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

		$file_pattern = '@\./[^_].+\.yaml$@';
		$dir = new \RecursiveDirectoryIterator($this->source);
		$ite = new \RecursiveIteratorIterator($dir);
		$yaml_files_iterator = new \RegexIterator($ite, $file_pattern, \RegexIterator::GET_MATCH);

		if ($target_info->getRealPath() === false) {
			// not exists
			$is_target_single = !$this->generator->canTargetMultiFile();
		} else {
			$is_target_single = !$target_info->isDir();
		}
		if ($is_target_single) {
			$this->iterator = $this->iterateDirToDir($yaml_files_iterator);
		} else {
			$this->iterator = $this->iterateDirToFile($yaml_files_iterator);
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

		foreach ($iterator as $file_match) {
			$target = $this->target . '/' . $this->replaceExtension($file_match, $this->generator->targetExtension());

			yield $file_match[0] => $target;
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

		foreach ($iterator as $file_match) {
			yield $file_match[0] => $this->target;
		}
	}

	protected function replaceExtension(string $source, string $target_extension)
	{
		$source_filename = basename($source);
		$source_extension_index = strrchr($source, '.');
		$target_filename = $source_extension_index < 0
			? $source_filename
			: (substr($source_filename, 0, $source_extension_index) . '.' . $target_extension);
		return dirname($source, $target_filename);
	}

	protected function createFileIterator()
	{
		$target = $this->target;
		$target_info = new \SplFileInfo($target);
		if ($target_info->isDir()) {
			$target = $target . basename($this->source);
		}
		$this->iterator = $this->iterateFile($target);
		$this->is_target_single = !$this->generator->canTargetMultiFile();
	}

	protected function iterateFile(string $target): \Generator
	{
		yield $this->source => $target;
	}
}
