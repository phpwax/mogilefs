<?php
use MogileFS\Exception;
use MogileFS\File\Mapper\Adapter\Base;
use MogileFS\File\Mapper\Adapter\Tracker;

/**
 * 
 * Test case for tracker (native) adapter
 * @author Jon Skarpeteig <jon.skarpeteig@gmail.com>
 * @package MogileFS
 * @group MogileFS
 * @group Adapter
 * @group Tracker
 */
class TrackerTest extends PHPUnit_Framework_TestCase
{
	protected $_configFile;
	protected $_tracker;

	public function setUp() {
    $this->test_file = realpath(__DIR__.'/../../../../example.txt');
    $config = [
      "domain"  => $GLOBALS["tracker_name"],
      "tracker" => [$GLOBALS["tracker_host"]],
			'noverify' => $GLOBALS["tracker_noverify"],
			'pathcount' => $GLOBALS["tracker_pathcount"],
      'request_timeout'=>$GLOBALS["tracker_timeout"]
    ];

		$this->_tracker = new Tracker($config);
	}

	public function testSaveAndDelete() {
		$key = 'testFile';
		$result = $this->_tracker->saveFile($key, $this->test_file, 'dev');
		$this->assertArrayHasKey('paths', $result);
		$this->assertArrayHasKey('fid', $result);
		$this->assertArrayHasKey('key', $result);
		$this->assertArrayHasKey('class', $result);
		$this->assertArrayHasKey('domain', $result);
		$this->_tracker->delete($key);
		$this->assertNull($this->_tracker->findPaths($key));
	}

	public function testFindInfo()
	{
		$key = 'testFile';
		$this->_tracker->saveFile($key, $this->test_file);
		$info = $this->_tracker->findInfo($key);
		$this->assertArrayHasKey('fid', $info);
		$this->assertArrayHasKey('class', $info);
		$this->assertArrayHasKey('size', $info);
		$this->assertEquals('default', $info['class']);
		$this->assertEquals(filesize($this->_configFile), $info['size']);
		$this->_tracker->delete($key);
		
		$this->assertNull($this->_tracker->findInfo($key));
	}

	public function testRename()
	{
		$key = 'testFile';
		$key2 = 'testFile2';
		$this->_tracker->saveFile($key, $this->test_file);
		try {
			$this->_tracker->rename($key, $key2);
		} catch (Exception $e) {
			// Clean up test data on failiure
			$this->_tracker->delete($key);
			throw $e;
		}

		$info = $this->_tracker->findInfo($key2);
		$this->_tracker->delete($key2);

		$this->assertNull($this->_tracker->findPaths($key));
		$this->assertEquals(filesize($this->_configFile), $info['size']);
	}

	public function testFetchAllPaths()
	{
		$key = 'testFile';
		$this->_tracker->saveFile($key, $this->test_file);
		$key2 = 'testFile2';
		$this->_tracker->saveFile($key2, $this->test_file);

		$pathsArray = $this->_tracker->fetchAllPaths(array(
					$key, $key2
				));
		$this->_tracker->delete($key);
		$this->_tracker->delete($key2);

		$this->assertArrayHasKey($key, $pathsArray);
		$this->assertArrayHasKey($key2, $pathsArray);
		$this->assertArrayHasKey('path1', $pathsArray[$key]);
		$this->assertArrayHasKey('path1', $pathsArray[$key2]);
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testFindPathsValidation()
	{
		$adapter = new Tracker();
		try {
			$adapter->findPaths(new Exception()); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testFetchAllPathsValidation()
	{
		$adapter = new Tracker();
		try {
			$adapter->fetchAllPaths(array(
						new Exception('')
					)); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testFindInfoValidation()
	{
		$adapter = new Tracker();
		try {
			$adapter->findInfo(new Exception('')); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testDeleteValidation()
	{
		$adapter = new Tracker();
		try {
			$adapter->delete(null); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testRenameValidation()
	{
		$adapter = new Tracker();
		try {
			$adapter->rename(null, 'asdf'); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testRename2Validation()
	{
		$adapter = new Tracker();
		try {
			$adapter->rename('asdf', null); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}
	
	/**
	* Argument validation test
	* Expecting MogileFS_Exception with 1XX code
	*/
	public function testSaveFileValidation()
	{
		$adapter = new Tracker();
		try {
			$adapter->saveFile(null, ''); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}
	
	/**
	* Argument validation test
	* Expecting MogileFS_Exception with 1XX code
	*/
	public function testSaveFile2Validation()
	{
		$adapter = new Tracker();
		try {
			$adapter->saveFile('key', '/tmp/me_N0_exist'); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}
}
