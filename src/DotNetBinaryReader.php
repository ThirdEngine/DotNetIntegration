<?php
/**
 * This class will give us the ability to read .NET binary files.
 *
 * @author Tony Vance, Third Engine Software
 */
namespace ThirdEngine\DotNetIntegration;


class DotNetBinaryReader
{
  /**
   * This method will read a 1-byte boolean from the stream.
   *
   * @param resource $stream
   * @return bool
   */
  public function readBool($stream)
  {
    $char = fread($stream, 1);
    return ord($char) == 1;
  }

  /**
   * This method will read a 16-byte decimal value from the stream.
   *
   * @param resource $stream
   * @return float
   */
  public function readDecimal($stream)
  {
    $value = fread($stream, 16);
    $bytes = [];

    for ($i = 0; $i < strlen($value); ++$i)
    {
      $bytes[$i] = ord($value[$i]);
    }

    $mantissaLength = 12;

    $base = 1;
    $value = 0;

    for ($i = 0; $i < $mantissaLength; ++$i)
    {
      $value += $bytes[$i] * $base;
      $base *= 256;
    }

    $signBit = $bytes[15] > 0;
    $exponent = $bytes[14];

    if ($signBit)
    {
      $value *= -1;
    }

    $value /= pow(10, $exponent);

    return $value;
  }

  /**
   * This method will read an int that uses the "7 bits at a time" convention where the high bit
   * determines if there is another byte to read.
   *
   * @param resource $stream
   * @return int
   */
  public function readCompactInt($stream)
  {
    $value = 0;
    $base = 1;

    while ($byte = ord(fread($stream, 1)))
    {
      $more = $byte & 128;
      $valuePart = $byte & ~128;

      $value += $base * $valuePart;

      if (!$more)
      {
        break;
      }

      $base *= 128;
    }

    return $value;
  }

  /**
   * This method will read an integer from the stream.
   *
   * @param resource $stream
   * @return int
   */
  public function readInt($stream, $test = false)
  {
    $byteString = fread($stream, 4);
    $bytes = [];

    for ($i=0; $i < strlen($byteString); ++$i)
    {
      $bytes[] = ord($byteString[$i]);
    }

    $bytes = array_reverse($bytes);
    $value = ($bytes[0] << 24) + ($bytes[1] << 16) + ($bytes[2] << 8) + $bytes[3];

    if ($value >= 2147483648)
    {
      $value -= 4294967296;
    }

    return $value;
  }

  /**
   * This method will read a string value from the stream.
   *
   * @param resource $stream
   * @return string
   */
  public function readString($stream)
  {
    $bytesToRead = $this->readCompactInt($stream);
    return $bytesToRead > 0 ? fread($stream, $bytesToRead) : '';
  }
}