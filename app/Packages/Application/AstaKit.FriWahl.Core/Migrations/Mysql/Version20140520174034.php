<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Migration to add the voting types and their relations.
 */
class Version20140520174034 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_votingproposalcandidate (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			proposal VARCHAR(40) DEFAULT NULL,
			candidate VARCHAR(40) DEFAULT NULL,
			position INT NOT NULL,

			INDEX IDX_EE705BC9BFE59472 (proposal),
			INDEX IDX_EE705BC9C8B28E44 (candidate),
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_votingproposalsupporter (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			proposal VARCHAR(40) DEFAULT NULL,
			supporter VARCHAR(40) DEFAULT NULL,
			position INT NOT NULL,

			INDEX IDX_2532BBD8BFE59472 (proposal),
			INDEX IDX_2532BBD83F06E55 (supporter),
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join (
			core_votinggroup VARCHAR(40) NOT NULL,
			core_voting VARCHAR(40) NOT NULL,

			INDEX IDX_CEED576E8DBC7A00 (core_votinggroup),
			UNIQUE INDEX UNIQ_CEED576E5C74B90C (core_voting),
			PRIMARY KEY(core_votinggroup, core_voting)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_person (
			persistence_object_identifier VARCHAR(40) NOT NULL,

			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_plebiscitequestion (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			plebiscite VARCHAR(40) DEFAULT NULL,
			question VARCHAR(255) NOT NULL,
			optionanswers LONGTEXT NOT NULL COMMENT '(DC2Type:array)',

			INDEX IDX_352EFDBCA22129A7 (plebiscite),
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_votingproposal (
			persistence_object_identifier VARCHAR(40) NOT NULL,
			voting VARCHAR(40) DEFAULT NULL,
			name VARCHAR(255) NOT NULL,
			shortname VARCHAR(255) NOT NULL,
			position INT NOT NULL,

			INDEX IDX_29697D7FFC28DA55 (voting),
			PRIMARY KEY(persistence_object_identifier)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposalcandidate
			ADD CONSTRAINT FK_EE705BC9BFE59472
			FOREIGN KEY (proposal)
			REFERENCES astakit_friwahl_core_domain_model_votingproposal (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposalcandidate
			ADD CONSTRAINT FK_EE705BC9C8B28E44
			FOREIGN KEY (candidate)
			REFERENCES astakit_friwahl_core_domain_model_eligiblevoter (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposalsupporter
			ADD CONSTRAINT FK_2532BBD8BFE59472
			FOREIGN KEY (proposal)
			REFERENCES astakit_friwahl_core_domain_model_votingproposal (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposalsupporter
			ADD CONSTRAINT FK_2532BBD83F06E55
			FOREIGN KEY (supporter)
			REFERENCES astakit_friwahl_core_domain_model_eligiblevoter (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join
			ADD CONSTRAINT FK_CEED576E8DBC7A00
			FOREIGN KEY (core_votinggroup)
			REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join
			ADD CONSTRAINT FK_CEED576E5C74B90C
			FOREIGN KEY (core_voting)
			REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_person
			ADD CONSTRAINT FK_24990F8947A46B0A
			FOREIGN KEY (persistence_object_identifier)
			REFERENCES typo3_party_domain_model_abstractparty (persistence_object_identifier)
			ON DELETE CASCADE");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_plebiscitequestion
			ADD CONSTRAINT FK_352EFDBCA22129A7
			FOREIGN KEY (plebiscite)
			REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposal
			ADD CONSTRAINT FK_29697D7FFC28DA55
			FOREIGN KEY (voting)
			REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			ADD list VARCHAR(40) DEFAULT NULL,
			ADD showlistname TINYINT(1) DEFAULT NULL
		");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			ADD CONSTRAINT FK_EC6D04AA44C8F818
			FOREIGN KEY (list)
			REFERENCES astakit_friwahl_core_domain_model_votingproposal (persistence_object_identifier)
		");

		$this->addSql("CREATE INDEX IDX_EC6D04AA44C8F818 ON astakit_friwahl_core_domain_model_voting (list)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposalcandidate
			DROP FOREIGN KEY FK_EE705BC9BFE59472");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_votingproposalsupporter
			DROP FOREIGN KEY FK_2532BBD8BFE59472");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			DROP FOREIGN KEY FK_EC6D04AA44C8F818");

		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_votingproposalcandidate");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_votingproposalsupporter");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_votinggroup_votings_join");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_person");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_plebiscitequestion");
		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_votingproposal");

		$this->addSql("DROP INDEX IDX_EC6D04AA44C8F818 ON astakit_friwahl_core_domain_model_voting");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_voting
			DROP list,
			DROP showlistname
		");
	}
}