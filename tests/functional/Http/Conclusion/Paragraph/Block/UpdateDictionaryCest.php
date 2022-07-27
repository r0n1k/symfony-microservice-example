<?php /** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph\Block\Dictionary;

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\UpdateDictionaries\DictionaryDTO;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\UpdateDictionaries\DTO;
use App\Tests\FunctionalTester;

class UpdateDictionaryCest
{
   private Block $block;
   private Paragraph $paragraph;
   private Conclusion $conclusion;
   private Project $project;
   /**
    * @var string
    */
   private string $url;
   /**
    * @var User
    */
   private User $user;

   public function _before(FunctionalTester $I)
   {
      $this->block = $I->have(Block::class, ['kind' => Kind::dict()]);
      $this->paragraph = $this->block->getParagraph();
      $this->conclusion = $this->paragraph->getConclusion();
      $this->project = $this->conclusion->getProject();
      $this->user = $I->amLoggedIn();
   }

   /** @skip */
   public function dictionariesCreate(FunctionalTester $I)
   {
   }

}
