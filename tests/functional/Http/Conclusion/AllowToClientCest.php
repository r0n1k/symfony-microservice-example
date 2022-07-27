<?php /** @noinspection JsonEncodingApiUsageInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFieldAssignmentTypeMismatchInspection */

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace App\Tests\Http\Conclusion;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Users\User\Role;
use App\Tests\FunctionalTester;

class AllowToClientCest
{

    public function _before(FunctionalTester $I)
    {
    }

    public function useCaseWorks(FunctionalTester $I)
    {
        /** @var Conclusion $conclusion */
        $conclusion = $I->have(Conclusion::class);
        $I->amLoggedInWithRole(Role::admin());
        $route = $I->grabRoute('conclusion.allow_to_client', ['conclusion_id' => $conclusion->getId()->getValue()]);

        $I->sendPUT($route, ['is_accessible' => true]);
        $I->seeResponseCodeIsSuccessful();
        $conclusion = $I->grabEntityFromRepository(Conclusion::class, ['id' => $conclusion->getId()->getValue()]);
        $I->assertTrue($conclusion->getIsAccessibleToClient());

        $I->sendPUT($route, ['is_accessible' => false]);
        $I->seeResponseCodeIsSuccessful();
        /** @var Conclusion $conclusion */
        $conclusion = $I->grabEntityFromRepository(Conclusion::class, ['id' => $conclusion->getId()->getValue()]);
        $I->assertFalse($conclusion->getIsAccessibleToClient());
    }

    public function disallowedIsInvisibleToClient(FunctionalTester $I)
    {
        /** @var Conclusion $conclusion */
        $conclusion = $I->have(Conclusion::class);
        $conclusion->setIsAccessibleToClient(false);
        $I->haveInRepository($conclusion);

        $route = $I->grabRoute('project.list_conclusions', ['project_id' => $conclusion->getProject()->getId()->getValue()]);
        $I->amLoggedInWithRole(Role::client());
        $I->sendGET($route);
        $I->seeResponseCodeIsSuccessful();
        $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);
        $I->assertCount(0, $response['data']);
    }

    public function allowedIsVisibleToClient(FunctionalTester $I)
    {
        /** @var Conclusion $conclusion */
        $conclusion = $I->have(Conclusion::class);
        $conclusion->setIsAccessibleToClient(true);
        $I->haveInRepository($conclusion);

        $route = $I->grabRoute('project.list_conclusions', ['project_id' => $conclusion->getProject()->getId()->getValue()]);
        $I->amLoggedInWithRole(Role::client());
        $I->sendGET($route);
        $I->seeResponseCodeIsSuccessful();
        $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);
        $I->assertCount(1, $response['data']);
    }
}
