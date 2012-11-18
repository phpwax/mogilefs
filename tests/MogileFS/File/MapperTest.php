<?php
use MogileFS\File;
use MogileFS\Exception;
use MogileFS\File\Mapper;
use MogileFS\File\Mapper\Adapter\Base;
use MogileFS\File\Mapper\Adapter\Test;

/**
 *
 * Test MogileFS_File_Mapper functions
 * @author Jon Skarpeteig <jon.skarpeteig@gmail.com>
 * @package MogileFS
 * @group MogileFS
 */
class MapperTest extends PHPUnit_Framework_TestCase
{
	protected $_key = 'someKey';
	protected $_testAdapter;
	protected $_testFile;
	protected $_resultSet;

	public function setUp()
	{
		$this->_testAdapter = new Test(
				array(
					'domain' => 'toast'
				));

		$this->_testFile = new File(
				array(
					'key' => $this->_key, 'file' => __DIR__.'/../../example.txt'
				));

		parent::setUp();
	}

	public function testSaveAndDelete()
	{
		$mapper = new Mapper(array(
			'adapter' => $this->_testAdapter
		));
		$savedFile = $mapper->save($this->_testFile);
		$this->assertInstanceOf('MogileFS\File', $savedFile);
		$this->assertNotNull($savedFile->getFid());
		$this->assertEquals('default', $savedFile->getClass());
		$this->assertEquals('toast', $savedFile->getDomain());
		$this->assertInternalType('array', $savedFile->getPaths());
		
		$key = $this->_testFile->getKey();
		$mapper->delete($key);
		$this->assertNull($mapper->find($key));
	}

	public function testFindAndFetchAll()
	{
		$mapper = new Mapper(array(
			'adapter' => $this->_testAdapter
		));
		$savedFile = $mapper->save($this->_testFile);

		$key = $this->_testFile->getKey();

		$file = $mapper->find($key);
		$this->assertInstanceOf('MogileFS\File', $file);
		$this->assertEquals('toast', $file->getDomain());

		$file = clone $this->_testFile;
		$file->setKey('n0suchk3y');
		$this->assertNull($mapper->find('NoSuchK3y'));
		$this->assertNull($mapper->findInfo($file));
		$this->assertNull($mapper->fetchAll(array(
							'NoSuchK3y'
						)));

		$result = $mapper->fetchAll(array(
					$this->_testFile->getKey()
				), true);
		
		$getFile = reset($result);
		
		// Don't download file - ignore comparison
		$getFile->setFile($savedFile->getFile());
		
		$this->assertEquals($savedFile, $getFile);
	}
	
	public function testFileLazyloader()
	{
		$mapper = new Mapper(array(
					'adapter' => $this->_testAdapter
		));
		$savedFile = $mapper->save($this->_testFile);
		
		$this->assertNotNull($savedFile->getClass(true));
		$this->assertNotNull($savedFile->getDomain(true));
		$this->assertNotNull($savedFile->getSize(true));
	}
	
	public function testFetchFile()
	{
		$this->_testFile->setPaths(array('http://127.0.0.1'));
		
		$mapper = new Mapper();
		$this->_testFile->setMapper($mapper);
		$this->assertFileExists($this->_testFile->getFile(true));
	}
	
	public function testGetAdapter()
	{
		$mapper = new Mapper(
				array(
					'defaultadapter' => 'MogileFS\File\Mapper\Adapter\Test', 'adapter' => array()
				));

		$this->assertInstanceOf('MogileFS\File\Mapper\Adapter\Test', $mapper->getAdapter());

		/**
		 * Argument validation test
		 * Expecting MogileFS_Exception with 1XX code
		 */
		$mapper = new Mapper();
		try {
			$mapper->getAdapter(); // No adapter set
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS\Exception exception');
	}


	/**
	* Argument validation test
	* Expecting MogileFS_Exception with 1XX code
	*/
	public function testSaveInvalidFile()
	{
		$mapper = new Mapper(array(
					'defaultadapter' => 'MogileFS\File\Mapper\Adapter\Test', 'adapter' => array()
				));
		try {
			$mapper->save(new File()); // Invalid file
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
	public function testFetchFileValidation()
	{
		$mapper = new Mapper();
		try {
			$mapper->fetchFile(new File()); // Invalid file
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}
}
