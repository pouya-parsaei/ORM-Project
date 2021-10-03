<?php

namespace Tests\Unit;

use App\Helpers\Config;
use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;
use App\Database\PDODatabaseConnection;

class PDOQueryBuilderTest extends TestCase
{
    private $queryBuilder;
    public function setUp(): void
    {
        $pdoConnection = new PDODatabaseConnection($this->getConfig());
        $this->queryBuilder = new PDOQueryBuilder($pdoConnection->connect());
        $this->queryBuilder->beginTransaction();
        parent::setUp();
    }

    public function testItCanCreateData()
    {
        $result = $this->insertIntoDb();
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testItCanUpdateData()
    {
        $this->insertIntoDb();
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'Pouya Parsaei')
            ->update(['email' => 'pppp@gmail.com', 'name' => 'First After Update']);
        $this->assertEquals(1, $result);
    }

    public function testItCanUpdateMultipleData()
    {
        $this->insertIntoDb();
        $this->insertIntoDb(['link' => 'http://test.multiple.update']);
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'Pouya Parsaei')
            ->where('link', 'http://link.com')
            ->update(['name' => 'after update']);
        $this->assertEquals(1, $result);
    }

    public function testItCanDeleteRecord()
    {
        $this->insertIntoDb();
        $this->insertIntoDb();
        $this->insertIntoDb();
        $this->insertIntoDb();
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'Pouya Parsaei')
            ->delete();
        $this->assertEquals(4, $result);
    }

    public function testItCanFetchData()
    {
        $this->multipleInsertIntoDb(10);
        $this->multipleInsertIntoDb(10, ['user' => 'Ali Ghochi']);
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('user', 'Ali Ghochi')
            ->get();
        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    public function testItCanGetSpecificColumns()
    {
        $this->multipleInsertIntoDb(10);
        $this->multipleInsertIntoDb(10, ['name' => 'New']);
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('name', 'New')
            ->get(['name', 'user']);
        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('name', $result[0]);
        $this->assertObjectHasAttribute('user', $result[0]);

        $result = json_decode(json_encode($result[0]), true);
        $this->assertEquals(['name', 'user'], array_keys($result));
    }
    public function testItCanGetFirstRow()
    {
        $this->multipleInsertIntoDb(10, ['name' => 'First Row']);
        $result = $this->queryBuilder
            ->table('bugs')
            ->where('name', 'First Row')
            ->first();
        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('email', $result);
        $this->assertObjectHasAttribute('link', $result);
        $this->assertObjectHasAttribute('id', $result);
        $this->assertObjectHasAttribute('user', $result);
    }

    public function testItCanFindWithID()
    {
        $this->insertIntoDb();
        $id = $this->insertIntoDb(['name'=>'For Find']);
        $result = $this->queryBuilder
        ->table('bugs')
        ->find($id);

        $this->assertIsObject($result);
        $this->assertEquals('For Find',$result->name);
    }

    public function testItCanFindBy()
    {
        $this->insertIntoDb();
        $id = $this->insertIntoDb(['name'=>'For Find By']);
        $result = $this->queryBuilder
        ->table('bugs')
        ->findBy('name','For Find By');

        $this->assertIsObject($result);
        $this->assertEquals($id,$result->id);
    }

    public function testItReturnsEmptyArrayWhenRecordNotFound()
    {
        $this->multipleInsertIntoDb(4);
        $result = $this->queryBuilder
        ->table('bugs')
        ->where('user','Dummy')
        ->get();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testItReturnsNullWhenFirstRecordNotFound()
    {
        $this->multipleInsertIntoDb(4);
        $result = $this->queryBuilder
        ->table('bugs')
        ->where('user','Dummy')
        ->first();
        $this->assertNull($result);   
    }

    public function testItReturnsZeroWhenRecordNotFoundForUpdate()
    {
        $this->multipleInsertIntoDb(4);
        $result = $this->queryBuilder
        ->table('bugs')
        ->where('user','Dummy')
        ->update(['name'=>'test']);
        $this->assertEquals(0,$result);
    }

    private function getConfig()
    {
        return Config::get('database', 'pdo_testing');
    }
    private function insertIntoDb($options = [])
    {
        $data = array_merge([
            'name' => 'First Bug Report',
            'link' => 'http://link.com',
            'user' => 'Pouya Parsaei',
            'email' => 'pouya@gmail.com'
        ], $options);
        return $this->queryBuilder->table('bugs')->create($data);
    }
    private function multipleInsertIntoDb($count, $options = [])
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->insertIntoDb($options);
        }
    }

    public function tearDown(): void
    {
        // $this->queryBuilder->truncateAllTable();
        $this->queryBuilder->rollback();
        parent::tearDown();
    }
}
