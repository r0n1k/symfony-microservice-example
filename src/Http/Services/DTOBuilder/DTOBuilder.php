<?php


namespace App\Http\Services\DTOBuilder;

use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOBuilder
{

   /**
    * @var Request
    */
   private $request;
   /**
    * @var SerializerInterface
    */
   private $serializer;
   /**
    * @var ValidatorInterface
    */
   private $validator;

   public function __construct(ValidatorInterface $validator)
   {
      $this->validator = $validator;

      $encoder = [new JsonEncoder()];
      $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
      $normalizer = [new ArrayDenormalizer(), new ObjectNormalizer(null, null, null, $extractor)];
      $this->serializer = new Serializer($normalizer, $encoder);
   }

   public function buildDTO($class, $ignoredFields = [])
   {
      $this->failOnNoRequest();
      $content = $this->request->getContent();

      if (!empty($content)) {
         try {
            return $this->serializer->deserialize($content, $class, 'json', [
               AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredFields,
               AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
            ]);
         } catch (NotNormalizableValueException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
         }
      }

      return new $class;
   }

   public function buildValidDTO($class, $ignoredFields = [], $additionalAttributes = [])
   {
      $this->failOnNoRequest();
      $dto = $this->buildDTO($class, $ignoredFields);
      foreach ($additionalAttributes as $key => $value) {
         $dto->$key = $value;
      }

      $violations = $this->validator->validate($dto);
      if ($violations->count()) {
         throw new InvalidDTOException($violations);
      }

      return $dto;
   }

   public function setRequest(Request $request): self
   {
      $this->request = $request;
      return $this;
   }

   private function failOnNoRequest()
   {
      if (!$this->request instanceof Request) {
         throw new RuntimeException('DTOBuilder has no request defined. DTOBuilder should be injected into action if it doesnt.');
      }
   }

   public function buildDTOFromArray(string $class, array $data, $ignoredFields = [])
   {
      return $this->serializer->denormalize($data, $class);
   }

   public function buildValidDTOFromArray(string $class, array $data, $ignoredFields = [], $additionalAttributes = [])
   {
      $dto = $this->buildDTOFromArray($class, $data, $ignoredFields);
      foreach ($additionalAttributes as $key => $value) {
         $dto->$key = $value;
      }

      $violations = $this->validator->validate($dto);
      if ($violations->count()) {
         throw new InvalidDTOException($violations);
      }

      return $dto;
   }
}
