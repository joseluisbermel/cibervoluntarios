<?php
namespace App\Tests\Serializer;

use App\Serializer\ValidationExceptionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationExceptionNormalizerTest extends TestCase
{
    private NormalizerInterface $decoratedMock;
    private ValidationExceptionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->decoratedMock = $this->createMock(NormalizerInterface::class);
        $this->normalizer = new ValidationExceptionNormalizer($this->decoratedMock);
    }

    public function testNormalizeWithValidationFailedException(): void
    {
        $violation1 = new ConstraintViolation('Error 1', null, [], '', 'property1', 'invalid value');
        $violation2 = new ConstraintViolation('Error 2', null, [], '', 'property2', 'invalid value');
        $violationList = new ConstraintViolationList([$violation1, $violation2]);

        $exception = new ValidationFailedException(null, $violationList);

        $this->decoratedMock
            ->expects($this->once())
            ->method('normalize')
            ->with($violationList, 'json', [])
            ->willReturn([
                ['property' => 'property1', 'message' => 'Error 1'],
                ['property' => 'property2', 'message' => 'Error 2']
            ]);

        $result = $this->normalizer->normalize($exception, 'json');

        $this->assertEquals(
            [
                'code' => 400,
                'message' => 'Validation Failed',
                'errors' => [
                    ['property' => 'property1', 'message' => 'Error 1'],
                    ['property' => 'property2', 'message' => 'Error 2']
                ]
            ],
            $result
        );
    }

    public function testNormalizeWithNonValidationException(): void
    {
        $data = new \stdClass();

        $this->decoratedMock
            ->expects($this->once())
            ->method('normalize')
            ->with($data, 'json', [])
            ->willReturn(['key' => 'value']);

        $result = $this->normalizer->normalize($data, 'json');

        $this->assertEquals(['key' => 'value'], $result);
    }

    public function testSupportsNormalizationWithValidationFailedException(): void
    {
        $exception = $this->createMock(ValidationFailedException::class);

        $this->assertTrue($this->normalizer->supportsNormalization($exception));
    }

    public function testSupportsNormalizationWithNonValidationException(): void
    {
        $data = new \stdClass();

        $this->decoratedMock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->with($data, 'json')
            ->willReturn(true);

        $this->assertTrue($this->normalizer->supportsNormalization($data, 'json'));
    }

    public function testSupportsNormalizationWithUnsupportedData(): void
    {
        $data = new \stdClass();

        $this->decoratedMock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->with($data, 'json')
            ->willReturn(false);

        $this->assertFalse($this->normalizer->supportsNormalization($data, 'json'));
    }
}
