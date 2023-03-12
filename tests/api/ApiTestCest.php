<?php 

class ApiTestCest
{
    public function _before(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
    }

    // tests
    public function xxxText(\ApiTester $I)
    {
        // $I->amHttpAuthenticated('service_user', '123456');
        // 请求类型
        $I->haveHttpHeader('Content-Type', 'application/json');
        // 发起请求 sendGET sendPOST, 第二个数组可传递参数
        $I->sendGET('/site/index', []);//
        // 判断http请求状态
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        // 判断是否返回json数据
        $I->seeResponseIsJson();
        // 判断返回json数据是否符合预期
        // $I->seeResponseContains('{"code":1,"msg":"ok"}');
        // 判断多层数据是否存在、例如code->name
        $I->seeResponseJsonMatchesJsonPath('$.code.name');
        // 判断数据类型
        $I->seeResponseMatchesJsonType([
            'code' => 'array',
            'msg' => 'string|null',
        ]);
    }
}
