<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Migration for the basic voting table.
 */
class Version20140515164414 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_voting (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			election VARCHAR(40) DEFAULT NULL,
			name VARCHAR(255) NOT NULL,
			dtype VARCHAR(255) NOT NULL,

			INDEX IDX_EC6D04AADCA03800 (election),
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			ADD CONSTRAINT FK_EC6D04AADCA03800
			FOREIGN KEY (election)
			REFERENCES astakit_friwahl_core_domain_model_election (persistence_object_identifier)"
		);
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_voting");
	}
}