<?php
namespace AstaKit\FriWahl\VoterDirectory\Domain\Model;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */


/**
 * Parser for a line from a file to import.
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportFileLineParser {

	/**
	 * Information on a line in the import file.
	 *
	 * @var array
	 */
	protected $formatInformation;

	/**
	 * The separator between fields, often used values are: , ; [TAB] [SPACE]
	 *
	 * @var string
	 */
	protected $fieldSeparator;

	/**
	 * The character used for wrapping a field's contents, e.g. " or '.
	 *
	 * @var string
	 */
	protected $fieldContentWrap;

	/**
	 * @param ImportFileFormat $formatInformation
	 */
	public function __construct(ImportFileFormat $formatInformation) {
		$this->formatInformation = $formatInformation;
	}

	/**
	 * Parses the given line from a file to import and returns the data correctly processed and mapped to the right
	 * fields.
	 *
	 * @param string $line
	 * @return array
	 * @throws \UnexpectedValueException
	 */
	public function parseLine($line) {
		$fields = str_getcsv($line, $this->formatInformation->getFieldSeparator(), $this->formatInformation->getFieldWrap());

		// TODO check if read line has correct number of fields

		$mappedLine = array(
			'properties' => array(),
			'discriminators' => array(),
		);

		foreach ($fields as $index => $fieldValue) {
			if (!$this->formatInformation->hasFieldWithIndex($index)) {
				// TODO write a test for undefined fields
				continue;
			}

			$fieldConfiguration = $this->formatInformation->getFieldConfiguration($index);

			if (isset($fieldConfiguration['skip']) && $fieldConfiguration['skip'] === TRUE) {
				// TODO write a test for this
				continue;
			}

			$fieldValue = $this->preProcessFieldValue($fieldConfiguration, $fieldValue);

			switch ($fieldConfiguration['type']) {
				case 'property':
					$mappedLine['properties'][$fieldConfiguration['name']] = $fieldValue;

					break;

				case 'discriminator':
					if (isset($fieldConfiguration['valueMap'])) {
						if (!isset($fieldConfiguration['valueMap'][$fieldValue])) {
							throw new \UnexpectedValueException(
								sprintf('Value %s not found in valueMap for field %s', $fieldValue, $fieldConfiguration['name']),
								1401456122
							);
						} else {
							$fieldValue = $fieldConfiguration['valueMap'][$fieldValue];
						}
					}

					$mappedLine['discriminators'][$fieldConfiguration['name']] = $fieldValue;

					break;
			}
		}

		return $mappedLine;
	}

	/**
	 * Does pre-processing for a field value, as configured in the key "preProcessing" in the field configuration.
	 * The key must be an array of processing instructions that will be handled in the exact order they are given.
	 *
	 * @param array $fieldConfiguration
	 * @param string $fieldValue
	 * @return string
	 *
	 * @throws \UnexpectedValueException If the preprocessing configuration is invalid
	 */
	protected function preProcessFieldValue($fieldConfiguration, $fieldValue) {
		if (!isset($fieldConfiguration['preProcessing'])) {
			return $fieldValue;
		}

		if (!is_array($fieldConfiguration['preProcessing'])) {
			throw new \UnexpectedValueException('pre-processing configuration must be an array');
		}

		// process the field values in the order they are specified, as this order might be significant (currently it
		// shouldn't be, but you never know…)
		foreach ($fieldConfiguration['preProcessing'] as $processor) {
			switch ($processor) {
				case 'trim':
					$fieldValue = trim($fieldValue);

					break;
			}
		}

		return $fieldValue;
	}
}
