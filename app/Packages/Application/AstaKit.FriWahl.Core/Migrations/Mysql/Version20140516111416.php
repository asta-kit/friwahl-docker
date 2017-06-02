<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Migration to add discriminator-related fields to votings.
 */
class Version20140516111416 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			ADD discriminator VARCHAR(255) NOT NULL,
			ADD discriminatorvalues LONGTEXT NOT NULL COMMENT '(DC2Type:array)',
			ADD discriminationmode INT NOT NULL"
		);
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			DROP discriminator,
			DROP discriminatorvalues,
			DROP discriminationmode"
		);
	}
}
