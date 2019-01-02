<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\SpecView;

class Endpoint extends AbstractTreeElement
{
	protected $input;
	protected $output;

	/**
	 * Endpoint constructor.
	 * @param SpecView $schema
	 */
	public function __construct(SpecView $schema)
	{
		parent::__construct($schema);
		$this->input = new Input($schema->getChild('input'));
		$this->output = new Output($schema->getChild('output'));
	}

	public function isRequired(): bool
	{
		return true;
	}

	/**
	 * @return Input
	 */
	public function getInput(): Input
	{
		return $this->input;
	}

	/**
	 * @return Output
	 */
	public function getOutput(): Output
	{
		return $this->output;
	}
}
