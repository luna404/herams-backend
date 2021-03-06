<?php


namespace prime\tests\functional\controllers\workspace;

use prime\models\ar\User;
use prime\models\permissions\Permission;
use prime\tests\FunctionalTester;

class ShareCest
{

    public function testAccessControl(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();

        $I->amOnPage(['workspace/share', 'id' => $workspace->id]);
        $I->seeResponseCodeIs(403);

        Permission::grant(User::findOne(['id' => TEST_USER_ID]), $workspace, Permission::PERMISSION_WRITE);

        $I->amOnPage(['workspace/share', 'id' => $workspace->id]);
        $I->seeResponseCodeIs(403);
    }

    public function testShareWithProjectWriteAccess(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();
        $user1 = User::findOne(['id' => TEST_USER_ID]);
        $user2 = User::findOne(['id' => TEST_ADMIN_ID]);
        Permission::grant(User::findOne(['id' => TEST_USER_ID]), $project, Permission::PERMISSION_WRITE);

        $I->amOnPage(['workspace/share', 'id' => $workspace->id]);
        $I->seeResponseCodeIsSuccessful();

        $I->seeElement(['id' => 'share-userids']);
        $I->selectOption(['id' => 'share-userids'], ['value' => TEST_ADMIN_ID]);

        $I->checkOption(['css' => '[name="Share[permission][]"][value=admin]']);
        $I->stopFollowingRedirects();
        $I->assertFalse(Permission::isAllowed($user2, $workspace, Permission::PERMISSION_ADMIN));
        $I->click('.btn-primary');
        $I->canSeeResponseCodeIs(302);
        $I->assertTrue(Permission::isAllowed($user2, $workspace, Permission::PERMISSION_ADMIN));
    }

    public function testShareWithProjectAdminAccess(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();
        $user1 = User::findOne(['id' => TEST_USER_ID]);
        $user2 = User::findOne(['id' => TEST_ADMIN_ID]);
        Permission::grant(User::findOne(['id' => TEST_USER_ID]), $project, Permission::PERMISSION_ADMIN);

        $I->amOnPage(['workspace/share', 'id' => $workspace->id]);

        $I->seeResponseCodeIsSuccessful();
        $I->seeElement(['id' => 'share-userids']);
        $I->selectOption(['id' => 'share-userids'], ['value' => TEST_ADMIN_ID]);

        $I->checkOption(['css' => '[name="Share[permission][]"][value=admin]']);
        $I->stopFollowingRedirects();
        $I->assertFalse(Permission::isAllowed($user2, $workspace, Permission::PERMISSION_ADMIN));
        $I->click('.btn-primary');
        $I->canSeeResponseCodeIs(302);
        $I->assertTrue(Permission::isAllowed($user2, $workspace, Permission::PERMISSION_ADMIN));
    }

    public function testShareWithAdminAccess(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $user1 = User::findOne(['id' => TEST_USER_ID]);
        $user2 = User::findOne(['id' => TEST_ADMIN_ID]);

        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();
        Permission::grant($user1, $workspace, Permission::PERMISSION_ADMIN);

        $I->amOnPage(['workspace/share', 'id' => $workspace->id]);
        $I->seeResponseCodeIs(200);

        $I->seeElement(['id' => 'share-userids']);
        $I->selectOption(['id' => 'share-userids'], ['value' => TEST_ADMIN_ID]);

        $I->checkOption(['css' => '[name="Share[permission][]"][value=admin]']);
        $I->stopFollowingRedirects();
        $I->assertFalse(Permission::isAllowed($user2, $workspace, Permission::PERMISSION_ADMIN));
        $I->click('.btn-primary');
        $I->canSeeResponseCodeIs(302);
        $I->assertTrue(Permission::isAllowed($user2, $workspace, Permission::PERMISSION_ADMIN));
    }
}