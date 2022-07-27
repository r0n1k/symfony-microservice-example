<?php
/** @noinspection PhpUnused */

namespace App\Tests\Helper;


use App\Domain\Template\TemplateFileRepository;
use App\Services\YamlParser;
use Codeception\Module;
use Codeception\TestInterface;
use Faker\Factory;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class ConclusionTemplates extends Module
{

   /**
    * @param $data
    * @return string
    */
   public function haveTemplate(array $data): string
   {
      $uuid = strtoupper(Factory::create()->uuid);
      $yaml = Yaml::dump($data);
      @mkdir($this->grabTemplatesDir(), 0777, true);
      $path = $this->grabTemplatesDir() . "{$uuid}.yaml";
      file_put_contents($path, $yaml);

      return $uuid;
   }

   public function _before(TestInterface $test)
   {
      $repo = new class(new YamlParser()) extends TemplateFileRepository {

         protected function basicTemplatesDirectory(): string
         {
            return $this->_test_dir;
         }

         public $_test_dir;
      };
      $repo->_test_dir = $this->grabTemplatesDir();
      try {
         /** @var Module\Symfony $symfony */
         $symfony = $this->getModule('Symfony');
      } catch (Throwable $e) {}
      if (isset($symfony)) {
         $symfony->_getContainer()->set(TemplateFileRepository::class, $repo);
      }
   }

   public function _after(TestInterface $test)
   {
      $this->rrmdir($this->grabTemplatesDir());
   }

   public function grabTemplatesDir()
   {
      return codecept_output_dir('templates/');
   }

   protected function rrmdir($dir)
   {
      if (is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
            if ($object !== '.' && $object !== '..') {
               if (filetype($dir . '/' . $object) === 'dir') {
                  $this->rrmdir($dir . '/' . $object);
               } else {
                  unlink($dir . '/' . $object);
               }
            }
         }
         reset($objects);
         rmdir($dir);
      }
   }
}
