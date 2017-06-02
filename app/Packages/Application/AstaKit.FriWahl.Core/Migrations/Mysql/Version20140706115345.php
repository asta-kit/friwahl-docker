<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;


/**
 * Migration to add the commit date for all votes (bugfix)
 */
class Version20140706115345 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP PROCEDURE IF EXISTS commit_vote");
		$this->addSql("
CREATE PROCEDURE `commit_vote` (
param_vote_id INT
)
  MODIFIES SQL DATA
BEGIN
-- ---------------
-- Error handling


-- -------------------
-- Update vote record
UPDATE astakit_friwahl_core_domain_model_vote
SET status = 2, datecommitted = NOW()
WHERE id = param_vote_id;

END");

	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP PROCEDURE IF EXISTS commit_vote");
		$this->addSql("
CREATE PROCEDURE `commit_vote` (
param_vote_id INT
)
  MODIFIES SQL DATA
BEGIN
-- ---------------
-- Error handling


-- -------------------
-- Update vote record
UPDATE astakit_friwahl_core_domain_model_vote
SET status = 2
WHERE id = param_vote_id;

END");

	}
}
 