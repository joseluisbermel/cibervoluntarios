<?php
namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationExceptionNormalizer implements NormalizerInterface
{
    private NormalizerInterface $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        if ($object instanceof ValidationFailedException) {
            return [
                'code' => 400,
                'message' => 'Validation Failed',
                'errors' => $this->decorated->normalize($object->getViolations(), $format, $context),
            ];
        }

        return $this->decorated->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof ValidationFailedException || $this->decorated->supportsNormalization($data, $format);
    }
}