<?php


namespace prime\tests\functional\controllers\workspace;

use prime\models\ar\User;
use prime\models\ar\Workspace;
use prime\models\permissions\Permission;
use prime\tests\FunctionalTester;
use yii\helpers\Url;
use yii\web\Request;

class DeleteCest
{

    public function testAccessControl(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();
        $user = User::findOne(['id' => TEST_USER_ID]);
        $I->createAndSetCsrfCookie('abc');
        $I->haveHttpHeader(Request::CSRF_HEADER, \Yii::$app->security->maskToken('abc'));
        $I->sendDELETE(Url::to(['/workspace/delete', 'id' => $workspace->id]));
        $I->seeResponseCodeIs(403);

        Permission::grant($user, $project, Permission::PERMISSION_READ);
        $I->sendDELETE(Url::to(['/workspace/delete', 'id' => $workspace->id]));
        $I->seeResponseCodeIs(403);

        Permission::grant($user, $workspace, Permission::PERMISSION_READ);
        $I->sendDELETE(Url::to(['/workspace/delete', 'id' => $workspace->id]));
        $I->seeResponseCodeIs(403);

        Permission::grant($user, $workspace, Permission::PERMISSION_WRITE);
        $I->sendDELETE(Url::to(['/workspace/delete', 'id' => $workspace->id]));
        $I->seeResponseCodeIs(403);
    }

    public function testDelete(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();

        Permission::grant(User::findOne(['id' => TEST_USER_ID]), $workspace, Permission::PERMISSION_ADMIN);

        $I->stopFollowingRedirects();
        $I->createAndSetCsrfCookie('abc');
        $I->haveHttpHeader(Request::CSRF_HEADER, \Yii::$app->security->maskToken('abc'));
        $I->sendDELETE(Url::to(['/workspace/delete', 'id' => $workspace->id]));

        $I->seeResponseCodeIs(302);
        $I->dontSeeRecord(Workspace::class, ['id' => $workspace->id]);
    }

    public function testDeleteProjectWrite(FunctionalTester $I)
    {
        $I->amLoggedInAs(TEST_USER_ID);
        $project = $I->haveProject();
        $workspace = $I->haveWorkspace();

        Permission::grant(User::findOne(['id' => TEST_USER_ID]), $project, Permission::PERMISSION_WRITE);

        $I->stopFollowingRedirects();
        $I->createAndSetCsrfCookie('abc');
        $I->haveHttpHeader(Request::CSRF_HEADER, \Yii::$app->security->maskToken('abc'));
        $I->sendDELETE(Url::to(['/workspace/delete', 'id' => $workspace->id]));

        $I->seeResponseCodeIs(302);
        $I->dontSeeRecord(Workspace::class, ['id' => $workspace->id]);
    }
}