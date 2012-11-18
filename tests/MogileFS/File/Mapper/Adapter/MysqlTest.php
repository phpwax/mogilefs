<?php
use MogileFS\Exception;
use MogileFS\File\Mapper\Adapter\Base;
use MogileFS\File\Mapper\Adapter\Mysql;
use MogileFS\File\Mapper\Adapter\Tracker;

/**
 * 
 * Test case for mysql read only adapter
 * @author Jon Skarpeteig <jon.skarpeteig@gmail.com>
 * @package MogileFS
 * @group MogileFS
 * @group Adapter
 * @group Tracker
 */
class MysqlAdapterTest extends PHPUnit_Framework_TestCase
{
	protected $_configFile;
	protected $_tracker;

	public function setUp()
	{
		$this->_configFile = realpath(dirname(__FILE__) . '/../../../config.php');
		$config = include $this->_configFile;
		$this->_mysqlAdapter = new Mysql($config['mysql']);
		$this->_trackerAdapter = new Tracker($config['tracker']);
	}

	public function testInstance()
	{
		$this
				->assertInstanceOf('MogileFS\File\Mapper\Adapter\Base',
						new Mysql());
	}

	public function testSettersAndGetters()
	{
		$adapter = new Mysql();
		$this
				->assertInstanceOf('MogileFS\File\Mapper\Adapter\Mysql',
						$adapter->setHostsUp(array(
									1, 2, 3, 4
								)));
		$this->assertEquals(array(
					1, 2, 3, 4
				), $adapter->getHostsUp());

	}

	public function testFetchAllPaths()
	{
		$key1 = 'testFile1';
		$file1 = $this->_trackerAdapter->saveFile($key1, $this->_configFile);
		$key2 = 'testFile2';
		$file2 = $this->_trackerAdapter->saveFile($key2, $this->_configFile);

		$files = $this->_mysqlAdapter->fetchAllPaths(array(
					$key1, $key2
				));
		$this->_trackerAdapter->delete($key1);
		$this->_trackerAdapter->delete($key2);

		$this->assertArrayHasKey($key1, $files);
		$this->assertArrayHasKey($key2, $files);
	}

	/**
	 * Argument validation test
	 * Expecting MogileFS_Exception with 1XX code
	 */
	public function testInvalidMysqlOptionsValidation()
	{
		$adapter = new Mysql(array(
			'domain' => 'toast'
		));
		try {
			$adapter->getMysql();
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
	public function testInvalidMysqlOptions2Validation()
	{
		$adapter = new Mysql(
				array(
					'domain' => 'toast', 'pdo_options' => 'host:lala'
				));
		try {
			$adapter->getMysql();
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
	public function testInvalidMysqlOptions3Validation()
	{
		$adapter = new Mysql(
				array(
					'domain' => 'toast', 'pdo_options' => 'host:lala', 'username' => 'mogile'
				));
		try {
			$adapter->getMysql();
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
		$adapter = new Mysql();
		try {
			$adapter->saveFile(null, '');
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
	public function testFindFileValidation()
	{
		$adapter = new Mysql();
		try {
			$adapter->findPaths('adsf');
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
		$adapter = new Mysql();
		try {
			$adapter->findInfo('adsf');
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
	public function testListKeysValidation()
	{
		$adapter = new Mysql();
		try {
			$adapter->listKeys('adsf', 'asdf2');
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
	public function testRenameFileValidation()
	{
		$adapter = new Mysql();
		try {
			$adapter->rename('adsf', 'asdf2');
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
	public function testDeleteFileValidation()
	{
		$adapter = new Mysql();
		try {
			$adapter->delete('adsf');
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
	public function testFetchAllPathsFileValidation()
	{
		$adapter = new Mysql();
		try {
			$adapter->fetchAllPaths(array(
						'arsf'
					));
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}
}
