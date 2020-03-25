<?php

namespace luya\testsuite\tests\cases;

use Yii;
use luya\testsuite\cases\WebApplicationTestCase;

final class WebApplicationTestCaseTest extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'webapptestcase',
            'basePath' => dirname(__DIR__),
            'components' => [
                'urlManager' => [
                    'cache' => null,
                ],
            ]
        ];
    }

    public function testInstance()
    {
        $this->assertInstanceOf('luya\web\Application', Yii::$app);
        $this->assertInstanceOf('luya\base\Boot', $this->boot);
        $this->assertInstanceOf('luya\web\Application', $this->app);
    }
}
