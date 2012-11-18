<?php
use MogileFS\File;
use MogileFS\Exception;
use MogileFS\File\Mapper;


/**
 *
 * Test MogileFS_File model
 * @author Jon Skarpeteig <jon.skarpeteig@gmail.com>
 * @package MogileFS
 * @group MogileFS
 * @group File
 */
class FileTest extends PHPUnit_Framework_TestCase
{
	protected $_testFile;

	public function getTestFile()
	{
		if (null === $this->_testFile || !file_exists($this->_testFile)) {
			$this->_testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('mogtest');
			file_put_contents($this->_testFile, 'Hello World!');
		}
		return $this->_testFile;
	}

	public function testSettersAndGetters()
	{
		$file = new File();
		$this->assertInstanceOf('MogileFS\File', $file->setClass('dev'));
		$this->assertInstanceOf('MogileFS\File', $file->setDomain('toast'));
		$this->assertInstanceOf('MogileFS\File', $file->setFid(123));
		$this->assertInstanceOf('MogileFS\File', $file->setFile($this->getTestFile()));
		$this->assertInstanceOf('MogileFS\File', $file->setKey('key'));
		$this->assertInstanceOf('MogileFS\File', $file->setMapper(new Mapper()));
		$this
				->assertInstanceOf('MogileFS\File',
						$file->setPaths(array(
									'http://mypath.com/123.fid'
								)));
		$this->assertInstanceOf('MogileFS\File', $file->setSize(321));

		$this->assertEquals('dev', $file->getClass());
		$this->assertEquals('toast', $file->getDomain());
		$this->assertEquals(123, $file->getFid());
		$this->assertEquals($this->getTestFile(), $file->getFile());
		$this->assertEquals('key', $file->getKey());
		$this->assertEquals(new Mapper(), $file->getMapper());
		$this->assertEquals(array(
					'http://mypath.com/123.fid'
				), $file->getPaths());
		$this->assertEquals(321, $file->getSize());
	}

	public function testIsValid()
	{
		$file = new File();
		$this->assertFalse($file->isValid(), 'File without any values cannot be valid');

		$file->setKey('key');
		$file->setFile($this->getTestFile());

		$this->assertTrue($file->isValid(), 'File with both key and file should be valid');
	}

	public function testToAndFromArray()
	{
		$fileArray = array(
				'class' => 'default',
				'domain' => 'toast',
				'fid' => 123,
				'file' => $this->getTestFile(),
				'key' => 'key',
				'paths' => array(
					'http://mypath.com/123.fid'
				),
				'size' => 321
		);
		$file = new File($fileArray);
		$this->assertEquals($fileArray, $file->toArray());
	}
	
	/**
	* Argument validation test
	* Expecting MogileFS_Exception with 1XX code
	*/
	public function testFileValidation()
	{
		$file = new File();
		try {
			$file->setFile('/tmp/me_n0_ex1st'); // Not valid value
		} catch (Exception $exc) {
			$this->assertLessThan(200, $exc->getCode(), 'Got unexpected exception code');
			$this->assertGreaterThanOrEqual(100, $exc->getCode(), 'Got unexpected exception code');
			return;
		}
		$this->fail('Did not get MogileFS_Exception exception');
	}
}
