<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Migration for the basic version of the EligibleVoter and VoterDiscriminator classes.
 */
class Version20140508192839 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_eligiblevoter (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			givenname VARCHAR(40) NOT NULL,
			familyname VARCHAR(40) NOT NULL,
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_voterdiscriminator (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			voter VARCHAR(40) DEFAULT NULL,
			identifier VARCHAR(255) NOT NULL,
			value VARCHAR(255) NOT NULL,
			INDEX IDX_83207C12268C4A59 (voter),
			UNIQUE INDEX UNIQ_83207C12268C4A59772E836A (voter, identifier),
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voterdiscriminator
			ADD CONSTRAINT FK_83207C12268C4A59
				FOREIGN KEY (voter)
				REFERENCES astakit_friwahl_core_domain_model_eligiblevoter (persistence_object_identifier)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voterdiscriminator DROP FOREIGN KEY FK_83207C12268C4A59");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_eligiblevoter");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_voterdiscriminator");
	}
}
