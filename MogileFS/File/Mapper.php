<?php
namespace MogileFS\File;

use MogileFS\File;
use MogileFS\Exception;
use MogileFS\File\Mapper\Adapter\Base;
use MogileFS\File\Mapper\Adapter\Tracker;
use MogileFS\File\Mapper\Adapter\Test;

/**
 * 
 * Mapper for MogileFS_File which populates file model with properties returned from adapters
 * @author Jon Skarpeteig <jon.skarpeteig@gmail.com>
 * @author Ross Riley <ross@oneblackbear.com>
 * @package MogileFS
 *
 */
class Mapper {
	/**
	 * 
	 * Configuration options for mapper
	 * @var array
	 */
	protected $_options;
	
	/**
	 * 
	 * Holds instance of adapter
	 * @var MogileFS_File_Mapper_Adapter_Abstract
	 */
	protected $_adapter;

	public function __construct(array $options = null)
	{
		if (null !== $options) {
			$this->setOptions($options);
		}
	}

	public function setOptions(array $options)
	{
		$this->_options = $options;
		return $this;
	}

	public function getOptions()
	{
		return $this->_options;
	}

	public function setAdapter(Base $adapter)
	{
		$this->_adapter = $adapter;
		return $this;
	}

	public function getAdapter()
	{
		if (!$this->_adapter instanceof Base) {
			$options = $this->getOptions();
			if (!isset($options['adapter'])) {
				throw new Exception(
						__METHOD__
								. ' No adapter set, and no \'adapter\' section with adapter options found',
						Exception::MISSING_OPTION);
			}

			if ($options['adapter'] instanceof Base) {
				$this->setAdapter($options['adapter']);
				return $this->_adapter;
			}
			
			$default = (isset($options['defaultadapter'])) ? $options['defaultadapter'] : 'Tracker';

			$adapterFile = str_replace('_', '/', $default).'.php';
			$this->setAdapter(new $default($options['adapter']));
		}
		return $this->_adapter;
	}

	public function find($key, $eagerLoad = false)
	{
		$file = new File();
		$file->setKey($key);
		$file->setMapper($this);

		$adapter = $this->getAdapter();
		$result = $adapter->findPaths($key);
		if (null === $result) {
			return null;
		}
		
		$file->setPaths($result);

		if (false !== $eagerLoad) {
			$info = $adapter->findInfo($key);
			$file->fromArray($info);
		}

		return $file;
	}

	public function findInfo(File $file)
	{
		$info = $this->getAdapter()->findInfo($file->getKey());
		if (null == $info) {
			return null;
		}
		$file->fromArray($info);
		return $file;
	}

	public function fetchAll(array $keys = null, $eagerLoad = false)
	{
		$files = array();
		$paths = $this->getAdapter()->fetchAllPaths($keys);
		if (empty($paths)) {
			return null;
		}
		foreach ($paths as $key => $pathArray) {
			$file = new File(array(
				'key' => $key,
				'paths' => $pathArray
			));
			$file->setMapper($this);
			if (false !== $eagerLoad) {
				$this->findInfo($file);
			}
			$files[$key] = $file;
		}
		return $files;
	}

	/**
	 * 
	 * Download file from MogileFS into a local temp file
	 * @param MogileFS_File $file
	 * @throws Exception
	 */
	public function fetchFile(File $file)
	{
		if (!$file->isValid()) {
			throw new Exception(
					__METHOD__ . ' Cannot fetch file from invalid file model',
					Exception::INVALID_ARGUMENT);
		}

		$localFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file->getKey();
		
		$fp = fopen($localFile, 'w');
		$url = reset($file->getPaths());

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		$file->setFile($localFile);
		return $file;
	}

	/**
	 * 
	 * Uploads file to MogileFS, and populate model with
	 * returned values (such as fid)
	 * @param MogileFS_File $file
	 * @throws Exception
	 * @return MogileFS_File stored in MogileFS
	 */
	public function save(File $file)
	{
		if (!$file->isValid()) {
			throw new Exception(__METHOD__ . ' Cannot save invalid file model',
					Exception::INVALID_ARGUMENT);
		}
		
		$file->setMapper($this);
		$this->getAdapter()
				->saveFile($file->getKey(), $file->getFile(false), $file->getClass(false));
		
		$storedFile = $this->find($file->getKey(), false);
		$storedFileArray = $storedFile->toArray();
		unset($storedFileArray['file']); // MogileFS has no information about local file
		$file->fromArray($storedFileArray);
		
		return $file;
	}

	public function delete($key)
	{
		$result = $this->getAdapter()->delete($key);
		return $result;
	}

}
