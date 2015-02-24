<?php
namespace ThirdEngine\DotNetIntegration;

use PHPUnit_Framework_TestCase;

class DotNetBinaryReaderTest extends PHPUnit_Framework_TestCase
{
  protected function getStreamWithBytes($bytes)
  {
    $stream = fopen('php://memory', 'r+');

    foreach ($bytes as $byte)
    {
      fwrite($stream, chr($byte));
    }

    rewind($stream);
    return $stream;
  }

  public function testReadBoolReturnsTrueForOne()
  {
    $stream = $this->getStreamWithBytes([1]);
    $reader = new DotNetBinaryReader();

    $this->assertTrue($reader->readBool($stream));
    fclose($stream);
  }

  public function testReadBoolReturnsFalseForZero()
  {
    $stream = $this->getStreamWithBytes([0]);
    $reader = new DotNetBinaryReader();

    $this->assertFalse($reader->readBool($stream));
    fclose($stream);
  }

  public function testReadDecimalReturnsCorrectValueForPositiveDecimal()
  {
    $stream = $this->getStreamWithBytes([57, 188, 190, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0]);
    $reader = new DotNetBinaryReader();

    $this->assertEquals(125000.25, $reader->readDecimal($stream));
    fclose($stream);
  }

  public function testReadDecimalReturnsCorrectValueForNegativeDecimal()
  {
    $stream = $this->getStreamWithBytes([147, 18, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 128]);
    $reader = new DotNetBinaryReader();

    $this->assertEquals(-47.55, $reader->readDecimal($stream));
    fclose($stream);
  }

  public function testReadIntReturnsCorrectValueForSmallNumber()
  {
    $stream = $this->getStreamWithBytes([20, 0, 0, 0]);
    $reader = new DotNetBinaryReader();

    $this->assertEquals(20, $reader->readInt($stream));
    fclose($stream);
  }

  public function testReadIntReturnsCorrectValueForMediumNumber()
  {
    $stream = $this->getStreamWithBytes([20, 1, 0, 0]);
    $reader = new DotNetBinaryReader();

    $this->assertEquals(276, $reader->readInt($stream));
    fclose($stream);
  }

  public function testReadStringReturnsStringValue()
  {
    $stream = $this->getStreamWithBytes([5, ord('H'), ord('e'), ord('l'), ord('l'), ord('o')]);
    $reader = new DotNetBinaryReader();

    $this->assertEquals('Hello', $reader->readString($stream));
    fclose($stream);
  }
}