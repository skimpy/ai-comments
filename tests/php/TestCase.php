<?php

namespace Tests\Skimpy\Comments;

use Skimpy\Comments\Entities\Comment;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $commentsManager;
    protected $comments;
    protected $defaultManager;

    public function createApplication()
    {
        return require __DIR__ . '/bootstrap.php';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->commentsManager = app('registry')->getManager('comments');
        $this->comments = $this->commentsManager->getRepository(Comment::class);
        $this->defaultManager = app('registry')->getManager();

        $this->rebuildDatabase();
    }

    protected function rebuildDatabase()
    {
        # Truncate the databases
        $managers = [
            'comments' => $this->commentsManager,
            'default' => $this->defaultManager
        ];

        foreach ($managers as $manager) {
            $connection = $manager->getConnection();
            $schemaManager = $connection->getSchemaManager();
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($manager);
            $classes = $manager->getMetadataFactory()->getAllMetadata();

            foreach ($schemaManager->listTableNames() as $tableName) {
                $schemaManager->dropTable($tableName);
            }

            # Recreate the schema
            $schemaTool->createSchema($classes);
        }
    }
}
