<?php

/**
 * This is the abstract model class for Logs to extend
 *
 * @author Thomas Dy <thatsmydoing@gmail.com>
 * @copyright Copyright &copy; 2010-2011 Ateneo de Manila University
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 */
abstract class AbstractLog extends CActiveRecord {
	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'log' => array(self::BELONGS_TO, 'Log', 'id'),
			'entries' => array(self::HAS_MANY, __CLASS__.'Entry', 'logId'),
		);
	}

	/**
	 * An event raised after loging
	 */
	public function onAfterLog($event) {
		$this->raiseEvent('onAfterLog', $event);
	}

	protected function afterLog() {
		if($this->hasEventHandler('onAfterLog')) {
			$this->onAfterLog(new CEvent($this));
		}
	}

	/**
	 * Run before deleting. Cascades deletions.
	 */
	protected function beforeDelete() {
		foreach($this->entries as $entry) {
			$entry->delete();
		}
		return parent::beforeDelete();
	}

	protected abstract function externalLabels();

	protected abstract function createSession($logId, $row);

	protected abstract function insertEntry($row);

	public function getEvents() {
		$events = array();
		foreach($this->entries as $entry) {
			$events[] = $this->getEvent($entry);
		}
		return $events;
	}

	protected abstract function getEvent($entry);

	/**
	 * Creates a new log and logs data into it
	 * @param integer id of the log
	 * @param array row containing log information
	 * @param CDbReader data source for the row data
	 */
	public function doLog($logId, $row, $reader) {
		$log = $this->createSession($logId, $row);
		foreach($reader as $row) {
			$log->insertEntry($row);
		}
		$log->afterLog();
	}

	/**
	 * Creates a new log if it does not already exist, and adds a
	 * row to it. Used for live loging.
	 * @param integer id of the log
	 * @param array the row to be added
	 */
	public function liveLog($logId, $row) {
		$log = $this->findByPk($logId);
		if($log == null) {
			$log = $this->createSession($logId, $row);
		}
		$log->insertEntry($row);
		$log->afterLog();
	}

	public function moveEntry($entry, $to) {
		$entry->logId = $to;
		if($entry->save()) {
			$this->afterLog();
			$entry->refresh();
			$toModel = $this->findByPk($to);
			if($toModel == null) {
				if(Log::model()->findByPk($to) != null) {
					$row = array();
					foreach($this->externalLabels() as $int => $ext) {
						if(isset($this->$int)) {
							$row[$ext] = $this->$int;
						}
					}
					$toModel = $this->createSession($to, $row);
					$toModel->afterLog();
				}
			}
			else {
				$toModel->afterLog();
			}
			return true;
		}
		return false;
	}

	public function deleteEntry($entry) {
		$entry->logId = -$this->id;
		if($entry->save()) {
			$this->afterLog();
			return true;
		}
		return false;
	}

	public function undeleteEntry($entry) {
		$entry->logId = $this->id;
		if($entry->save()) {
			$this->afterLog();
			return true;
		}
		return false;
	}

	/**
	 * Generates a CSV file containing the data for this log.
	 * @param file the file pointer to write to
	 */
	public function doExport($fp) {
		$extToInt = array_flip($this->externalLabels());
		$extHeaders = array_keys($extToInt);
		fputcsv($fp, $extHeaders);
		$counter = 1;
		foreach($this->entries as $entryModel) {
			$toWrite = array();
			foreach($extHeaders as $extHeader) {
				if(array_key_exists($extHeader, $extToInt)) {
					$intLabel = $extToInt[$extHeader];
					if(array_key_exists($intLabel, $entryModel->attributes)) {
						$toWrite[] = $entryModel->attributes[$intLabel];
					}
					else if (array_key_exists($intLabel, $this->attributes)) {
						$toWrite[] = $this->attributes[$intLabel];
					}
					else {
						$toWrite[] = '';
					}
				}
				else if($extHeader == 'id') {
					$toWrite[] = $counter++;
				}
				else if($extHeader == 'revision') {
					$toWrite[] = 0;
				}
				else {
					$toWrite[] = '';
				}
			}
			fputcsv($fp, $toWrite);
		}
	}
}

