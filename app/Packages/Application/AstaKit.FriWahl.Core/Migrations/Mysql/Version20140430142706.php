<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Migration for the basic election and ballot box models
 */
class Version20140430142706 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_ballotbox (persistence_object_identifier VARCHAR(40) NOT NULL, election VARCHAR(40) DEFAULT NULL, name VARCHAR(255) NOT NULL, boxgroup VARCHAR(255) NOT NULL, status INT NOT NULL, INDEX IDX_14BFDB15DCA03800 (election), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_election (persistence_object_identifier VARCHAR(40) NOT NULL, name VARCHAR(255) NOT NULL, created DATETIME NOT NULL, test TINYINT(1) NOT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_electionperiod (persistence_object_identifier VARCHAR(40) NOT NULL, election VARCHAR(40) DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, INDEX IDX_7FD34A7BDCA03800 (election), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_ballotbox ADD CONSTRAINT FK_14BFDB15DCA03800 FOREIGN KEY (election) REFERENCES astakit_friwahl_core_domain_model_election (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_electionperiod ADD CONSTRAINT FK_7FD34A7BDCA03800 FOREIGN KEY (election) REFERENCES astakit_friwahl_core_domain_model_election (persistence_object_identifier)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_ballotbox DROP FOREIGN KEY FK_14BFDB15DCA03800");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_electionperiod DROP FOREIGN KEY FK_7FD34A7BDCA03800");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_ballotbox");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_election");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_electionperiod");
	}
}