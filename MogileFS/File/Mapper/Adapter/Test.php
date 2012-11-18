<?php
namespace MogileFS\File\Mapper\Adapter;
use MogileFS\Exception;
/**
 *
 * Test adapter for MogileFS
 * @author Jon Skarpeteig <jon.skarpeteig@gmail.com>
 * @package MogileFS
 *
 */
class Test extends Base
{
	protected $_saveResult = array();

	public function findPaths($key) {
		return isset($this->_saveResult[$key]['paths']) ? $this->_saveResult[$key]['paths'] : null;
	}

	public function findInfo($key) {
		return isset($this->_saveResult[$key]) ? $this->_saveResult[$key] : null;
	}

	public function fetchAllPaths(array $keys) {
		$result = array();
		foreach ($keys as $key) {
			$paths = $this->findPaths($key);
			if (null !== $paths) {
				$result[$key] = $this->findPaths($key);
			}
		}
		return $result;
	}

	public function saveFile($key, $file, $class = null) {
		$options = $this->getOptions();
		if (!isset($options['domain'])) {
			throw new Exception(
					__METHOD__ . ' Mandatory option \'domain\' missing from options',
					Exception::MISSING_OPTION);
		}

		$fid = rand(0, 1000);
		$this->_saveResult[$key] = array(
				'fid' => $fid,
				'key' => $key,
				'size' => 123,
				'paths' => array(
					'file://' . $file
				),
				'domain' => $options['domain'],
				'class' => (null === $class) ? 'default' : $class
		);

		return $this->_saveResult[$key];
	}

	public function rename($fromKey, $toKey) {
		if (isset($this->_saveResult[$fromKey])) {
			$this->_saveResult[$toKey] = $this->_saveResult[$fromKey];
			unset($this->_saveResult[$fromKey]);
		}
		return;
	}

	public function delete($key) {
		if (isset($this->_saveResult[$key])) {
			unset($this->_saveResult[$key]);
		}
		return;
	}
}
