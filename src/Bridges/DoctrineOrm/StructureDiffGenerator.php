<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    New BSD License
 * @link       https://github.com/nextras/migrations
 */

namespace Nextras\Migrations\Bridges\DoctrineOrm;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nextras;
use Nextras\Migrations\IDiffGenerator;


class StructureDiffGenerator implements IDiffGenerator
{

	/** @var EntityManager */
	private $entityManager;

	/** @var string|NULL absolute path to a file */
	private $ignoredQueriesFile;


	/**
	 * @param EntityManager $entityManager
	 * @param string|NULL   $ignoredQueriesFile
	 */
	public function __construct(EntityManager $entityManager, $ignoredQueriesFile = NULL)
	{
		$this->entityManager = $entityManager;
		$this->ignoredQueriesFile = $ignoredQueriesFile;
	}


	/**
	 * @return string
	 */
	public function getExtension()
	{
		return 'sql';
	}


	/**
	 * @return string
	 */
	public function generateContent()
	{
		$queries = array_diff($this->getUpdateQueries(), $this->getIgnoredQueries());
		$content = $queries ? (implode(";\n", $queries) . ";\n") : '';

		return $content;
	}


	/**
	 * @return string[]
	 */
	protected function getUpdateQueries()
	{
		$schemaTool = new SchemaTool($this->entityManager);
		$metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
		$queries = $schemaTool->getUpdateSchemaSql($metadata, TRUE);

		return $queries;
	}


	/**
	 * @return string[]
	 */
	protected function getIgnoredQueries()
	{
		if ($this->ignoredQueriesFile === NULL) {
			return [];
		}

		$content = file_get_contents($this->ignoredQueriesFile);
		$queries = preg_split('~(\s*;\s*\r?\n|\z)~', $content, -1, PREG_SPLIT_NO_EMPTY);

		return $queries;
	}

}
