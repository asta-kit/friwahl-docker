<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Migration to add the connection between voters and their election
 */
class Version20140515180724 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_eligiblevoter
			ADD election VARCHAR(40) DEFAULT NULL"
		);
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_eligiblevoter
			ADD CONSTRAINT FK_C5D59B3EDCA03800
				FOREIGN KEY (election)
				REFERENCES astakit_friwahl_core_domain_model_election (persistence_object_identifier)"
		);
		$this->addSql("CREATE INDEX IDX_C5D59B3EDCA03800
			ON astakit_friwahl_core_domain_model_eligiblevoter (election)"
		);
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_eligiblevoter
			DROP FOREIGN KEY FK_C5D59B3EDCA03800"
		);
		$this->addSql("DROP INDEX IDX_C5D59B3EDCA03800 ON astakit_friwahl_core_domain_model_eligiblevoter");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_eligiblevoter DROP election");
	}
}