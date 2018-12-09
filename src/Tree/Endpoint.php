<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;


use Turenar\ApiSchema\SpecView;

class Endpoint implements TreeElement
{
	protected $input;
	protected $output;

	/**
	 * Endpoint constructor.
	 * @param SpecView $schema
	 * @throws \Turenar\ApiSchema\SpecException
	 */
	public function __construct(SpecView $schema)
	{
		$this->input = new Input($schema->getChild('input'));
		$this->output = new Output($schema->getChild('output'));
	}

	public function getSchema()
	{
		return ['input' => $this->input->getSchema(), 'output' => $this->output->getSchema()];
	}

	public function isRequired()
	{
		return true;
	}
}
