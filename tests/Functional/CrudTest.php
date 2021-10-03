<?php

namespace Tests\Functional;

use App\Helpers\Config;
use App\Helpers\HttpClient;
use PHPUnit\Framework\TestCase;
use App\Database\PDOQueryBuilder;
use App\Database\PDODatabaseConnection;

class CrudTest extends TestCase
{
    private $httpClient;
    private $queryBuilder;
    public function setUp(): void
    {
        $pdoConnection = new PDODatabaseConnection($this->getConfig());
        $this->queryBuilder = new PDOQueryBuilder($pdoConnection->connect());
        $this->httpClient = new HttpClient();
        parent::setUp();
    }

    public function testItCanCreateDataWithAPI()
    {
        $data = [
            'json' => [
                'name' =>  'API',
                'email' =>  'pouya@gmail.com',
                'link' =>  'http://api',
                'user' => 'pouya'

            ]
        ];
        $response = $this->httpClient->post('http://localhost/17-tdd/02-bug-tracker/index.php', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $bug = $this->queryBuilder
            ->table('bugs')
            ->where('name', 'API')
            ->where('user', 'Pouya')
            ->first();
        $this->assertNotNull($bug);
        return $bug;
    }

    /**
    * @depends testItCanCreateDataWithAPI
    */
    public function testItCanUpdateDataWithAPI($bug)
    {
        $data = [
            'json'=>[
                'id' => $bug->id,
                'name'=> 'API For Update'
            ]
            ];
            $response = $this->httpClient->put('http://localhost/17-tdd/02-bug-tracker/index.php',$data);
            $this->assertEquals(200,$response->getStatusCode());
            $bug = $this->queryBuilder
            ->table('bugs')
            ->find($bug->id);
            $this->assertNotNull($bug);

    }

    /**
    * @depends testItCanCreateDataWithAPI
    */
    public function testItCanFetchDataWithAPI($bug)
    {
        $response = $this->httpClient->get('http://localhost/17-tdd/02-bug-tracker/index.php',[
            'json' =>[
                'id'=>$bug->id
            ]
        ]);
        $this->assertEquals(200,$response->getStatusCode());
        $this->assertArrayHasKey('id',json_decode($response->getBody(),true));
    }

    /**
    * @depends testItCanCreateDataWithAPI
    */
    public function testItCanDeleteDataWithAPI($bug)
    {
        $response = $this->httpClient->delete('http://localhost/17-tdd/02-bug-tracker/index.php',[
            'json' =>[
                'id' =>$bug->id
            ]
            ]);
            $this->assertEquals(204,$response->getStatusCode());
            $bug = $this->queryBuilder
            ->table('bugs')
            ->find($bug->id);
            $this->assertNull($bug);

    }
    public function tearDown(): void
    {
        $this->httpClient = null;
        parent::tearDown();
    }
    private function getConfig()
    {
        return Config::get('database', 'pdo_testing');
    }
}
