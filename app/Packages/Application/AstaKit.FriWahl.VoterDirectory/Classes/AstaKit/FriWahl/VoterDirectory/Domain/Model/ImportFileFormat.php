<?php
namespace AstaKit\FriWahl\VoterDirectory\Domain\Model;

/*                                                                                 *
 * This script belongs to the TYPO3 Flow package "AstaKit.FriWahl.VoterDirectory". *
 *                                                                                 *
 *                                                                                 */


/**
 * Format description for a file to import voters from.
 *
 * Each format description can contain an arbitrary number of fields, which can be mapped to either a property or a
 * discriminator in the target voter object.
 *
 * The fields defined for the format are indexed numerically. A possible extension would be to index them by name and
 * make it possible to arbitrarily map the fields in the import file by supplying the field order in the first line (as
 * is usually done for CSV files).
 *
 * @author Andreas Wolf <andreas.wolf@usta.de>
 */
class ImportFileFormat {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * The character used to separate fields in a line. This must be exactly one single character, mainly because
	 * the method str_getcsv() we use for reading a line does not support multiple character separators.
	 *
	 * @var string
	 */
	protected $fieldSeparator;

	/**
	 * The character used to wrap a fields' contents.
	 *
	 * @var string
	 */
	protected $fieldWrap;

	/**
	 * Field definitions.
	 *
	 * @var array
	 */
	protected $fields;

	/**
	 * Constructor method to create an object from a configuration entry defining a file format
	 *
	 * @param string $formatName
	 * @param array $importConfiguration
	 * @return self
	 *
	 * @throws \InvalidArgumentException If the configuration is invalid
	 */
	public static function createFromConfiguration($formatName, $importConfiguration) {
		$fileFormat = new ImportFileFormat();

		if (!isset($importConfiguration['fieldSeparator']) || !isset($importConfiguration['fieldWrap'])) {
			throw new \InvalidArgumentException('Field separator or field wrap not defined for format ' . $formatName, 1401554952);
		}
		if (strlen($importConfiguration['fieldSeparator']) != 1) {
			throw new \InvalidArgumentException('Field separator must be exactly one character in format ' . $formatName, 1401554953);
		}
		if (strlen($importConfiguration['fieldWrap']) != 1) {
			throw new \InvalidArgumentException('Field wrap must be exactly one character in format ' . $formatName, 1401554954);
		}
		if (!isset($importConfiguration['fields']) || count($importConfiguration['fields']) == 0) {
			throw new \InvalidArgumentException('No fields defined for format ' . $formatName, 1401554955);
		}

		$fileFormat->fields = $importConfiguration['fields'];
		$fileFormat->fieldSeparator = $importConfiguration['fieldSeparator'];
		$fileFormat->fieldWrap = $importConfiguration['fieldWrap'];

		return $fileFormat;
	}

	/**
	 * Use createFromConfiguration().
	 */
	protected function __construct() {
	}

	/**
	 * @return string
	 */
	public function getFieldSeparator() {
		return $this->fieldSeparator;
	}

	/**
	 * @return string
	 */
	public function getFieldWrap() {
		return $this->fieldWrap;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the number of defined fields in the input file.
	 *
	 * @return int
	 */
	public function getInputFieldCount() {
		return count($this->fields);
	}

	/**
	 * Returns TRUE if this format maps one of the input fields to the given property
	 *
	 * @param string $property The property to check
	 * @return bool
	 */
	public function hasMappingForProperty($property) {
		foreach ($this->fields as $field) {
			if ($field['type'] == 'property' && $field['name'] == $property) {
				//
			}
		}
		return FALSE;
	}

	/**
	 * Returns the configuration for the given field index
	 *
	 * @param int $index
	 * @return array
	 */
	public function getFieldConfiguration($index) {
		return $this->fields[$index];
	}

	public function hasFieldWithIndex($index) {
		return isset($this->fields[$index]);
	}

}
