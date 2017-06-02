<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Migration to change the voting groups–votings relation to 1:n.
 *
 * Note that this does not migrate existing relations!
 */
class Version20140608184305 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting ADD votinggroup VARCHAR(40) DEFAULT NULL");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting ADD CONSTRAINT FK_EC6D04AA5A8DE46A FOREIGN KEY (votinggroup) REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("CREATE INDEX IDX_EC6D04AA5A8DE46A ON astakit_friwahl_core_domain_model_voting (votinggroup)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join (core_votinggroup VARCHAR(40) NOT NULL, core_voting VARCHAR(40) NOT NULL, UNIQUE INDEX UNIQ_CEED576E5C74B90C (core_voting), INDEX IDX_CEED576E8DBC7A00 (core_votinggroup), PRIMARY KEY(core_votinggroup, core_voting)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join ADD CONSTRAINT FK_CEED576E5C74B90C FOREIGN KEY (core_voting) REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join ADD CONSTRAINT FK_CEED576E8DBC7A00 FOREIGN KEY (core_votinggroup) REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting DROP FOREIGN KEY FK_EC6D04AA5A8DE46A");
		$this->addSql("DROP INDEX IDX_EC6D04AA5A8DE46A ON astakit_friwahl_core_domain_model_voting");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting DROP votinggroup");
	}
}