<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Migration to add the vote class and corresponding stored procedures.
 */
class Version20140609162100 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP PROCEDURE IF EXISTS register_vote");
		$this->addSql("
CREATE PROCEDURE `register_vote` (
	param_voter_id VARCHAR(40),
	param_voting_id VARCHAR(40),
	param_ballotbox_id VARCHAR(40)
)
	MODIFIES SQL DATA
BEGIN
	-- ---------------
	-- Error handling

	-- duplicate key error - voter apparently has voted already (or is registered as a voter)
	DECLARE EXIT HANDLER FOR 1062
		SELECT \"Voter has already participated\" AS error_msg, 1402411794 AS error_code;
	-- TODO handle deadlock errors (error 1213)

	-- ----------------
	-- Record creation
	INSERT INTO astakit_friwahl_core_domain_model_vote
		(voter, voting, ballotbox, datecreated, status)
	VALUES (
		param_voter_id,
		param_voting_id,
		param_ballotbox_id,
		NOW(),
		1 -- queued vote (as opposed to an already committed vote which would be 2)
	);

	SELECT LAST_INSERT_ID() AS vote_id;

END");

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

		$this->addSql("DROP PROCEDURE IF EXISTS commit_votes_for_voter");
		$this->addSql('
-- cancels the pending votes for the given voter
CREATE PROCEDURE `commit_votes_for_voter` (
	param_voter_id VARCHAR(40),
	param_ballotbox_id VARCHAR(40)
)
	MODIFIES SQL DATA
BEGIN
	START TRANSACTION;
		UPDATE astakit_friwahl_core_domain_model_vote
			SET status = 2, datecommitted = NOW()
			WHERE voter = param_voter_id
				AND ballotbox = param_ballotbox_id
				AND status = 1;

		SELECT ROW_COUNT() AS committed_votes;
	COMMIT;

END');

		$this->addSql("DROP PROCEDURE IF EXISTS cancel_votes_for_voter");
		$this->addSql('
-- cancels the pending votes for the given voter
CREATE PROCEDURE `cancel_votes_for_voter` (
	param_voter_id VARCHAR(40),
	param_ballotbox_id VARCHAR(40)
)
	MODIFIES SQL DATA
BEGIN
	START TRANSACTION;
		DELETE FROM astakit_friwahl_core_domain_model_vote
			WHERE voter = param_voter_id
				AND ballotbox = param_ballotbox_id
				AND status = 1;

		SELECT ROW_COUNT() AS cancelled_votes;
	COMMIT;

END');

		$this->addSql("CREATE TABLE astakit_friwahl_core_domain_model_vote (
			id INT AUTO_INCREMENT NOT NULL,
			voter VARCHAR(40) DEFAULT NULL,
			voting VARCHAR(40) DEFAULT NULL,
			ballotbox VARCHAR(40) DEFAULT NULL,
			datecreated DATETIME NOT NULL,
			datecommitted DATETIME NOT NULL,
			status INT NOT NULL,

			INDEX IDX_4151BA22268C4A59 (voter),
			INDEX IDX_4151BA22FC28DA55 (voting),
			INDEX IDX_4151BA22B32AB266 (ballotbox),

			UNIQUE INDEX UNIQ_4151BA22268C4A59FC28DA55 (voter, voting),
			PRIMARY KEY(id)
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_vote
			ADD CONSTRAINT FK_4151BA22268C4A59
				FOREIGN KEY (voter)
				REFERENCES astakit_friwahl_core_domain_model_eligiblevoter (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_vote
			ADD CONSTRAINT FK_4151BA22FC28DA55
				FOREIGN KEY (voting)
				REFERENCES astakit_friwahl_core_domain_model_voting (persistence_object_identifier)");
		$this->addSql("ALTER TABLE astakit_friwahl_core_domain_model_vote
			ADD CONSTRAINT FK_4151BA22B32AB266
				FOREIGN KEY (ballotbox)
				REFERENCES astakit_friwahl_core_domain_model_ballotbox (persistence_object_identifier)");

	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql('DROP PROCEDURE register_vote');
		$this->addSql('DROP PROCEDURE commit_vote');
		$this->addSql('DROP PROCEDURE commit_votes_for_voter');
		$this->addSql('DROP PROCEDURE cancel_votes_for_voter');

		$this->addSql("DROP TABLE astakit_friwahl_core_domain_model_vote");
	}
}
 