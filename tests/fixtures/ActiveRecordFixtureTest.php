<?php

namespace luya\testsuite\tests\fixtures;

use luya\testsuite\cases\BaseTestSuite;
use luya\base\Boot;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\tests\data\TestModel;

class ActiveRecordFixtureTest extends BaseTestSuite
{
    public function getConfigArray()
    {
        return [
            'id' => 'basetestcase',
            'basePath' => dirname(__DIR__),
            'components' => [
                'urlManager' => [
                    'cache' => null,
                ],
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ];
    }
    
    public function bootApplication(Boot $boot)
    {
        $boot->applicationWeb();
    }
    
    public function testLoadSchemaFromRules()
    {
        $model = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'removeSafeAttributes' => true,
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
                'group_id' => 1,
            ]]
        ]);

        $this->assertSame([
            'user_id' => 'integer',
            'group_id' => 'integer',
            'text' => 'text',
            'is_deleted' => 'boolean',
            'switch' => 'integer',
        ], $model->getSchema());
        
        // try to add new record
        
        $user = $model->getNewModel();
        $user->attributes = ['id' => 2, 'user_id' => 1, 'group_id' => 1];
        $this->assertTrue($user->insert());
        
        // try to load data from model with fixture
        
        $select = $model->getModel('model1');
        
        $this->assertSame(1, $select->id);
        
        $model->cleanup();
    }

    public function testLoadSchemaFromRulesWithSafe()
    {
        $model = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
            ]]
        ]);

        $this->assertSame([
            'user_id' => 'integer',
            'text' => 'text',
            'is_deleted' => 'boolean',
            'switch' => 'integer',
            'hidden' => 'text',
        ], $model->getSchema());
        
        $model->cleanup();
    }

    public function testRebuild()
    {
        $this->assertNull($this->app->db->schema->getTableSchema(TestModel::tableName()));
        
        $model = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
            ]]
        ]);

        
        $model->rebuild();
        $this->assertNotEmpty($this->app->db->schema->getTableSchema(TestModel::tableName()));
    }

    public function testSkipIfExists()
    {
        $model1 = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
            ]]
        ]);

        $this->assertNotEmpty($this->app->db->schema->getTableSchema(TestModel::tableName()));

        $model2 = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'fixtureData' => ['model1' => [
                'id' => 2,
                'user_id' => 1,
            ]]
        ]);

        $this->assertNotEmpty($this->app->db->schema->getTableSchema(TestModel::tableName()));

        $model1->cleanup();
        $model2->cleanup();
        $this->assertNotEmpty($model1);
        $this->assertNotEmpty($model2);
    }

    public function testSkipIfExistsException()
    {
        $model1 = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
            ]]
        ]);
        $this->expectException('\yii\db\Exception');
        $model2 = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'skipIfExists' => false,
            'fixtureData' => ['model1' => [
                'id' => 2,
                'user_id' => 1,
            ]]
        ]);
    }
}
