<?php

namespace Tests\Skimpy\Comments\AI;

use Mockery;
use Skimpy\Repo\Entries;
use Skimpy\CMS\ContentItem;
use Tests\Skimpy\Comments\TestCase;
use Illuminate\Console\OutputStyle;
use Skimpy\Comments\Entities\Comment;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Skimpy\Comments\AI\CreateAgentEntryComments;
use Symfony\Component\Console\Output\BufferedOutput;
use Skimpy\Comments\AI\CommentWriterInterface;

class CreateAgentEntryCommentsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_agent_comments_for_a_single_entry()
    {
        $registry = $this->app->make('registry');

        $entry = new ContentItem(
            'example-entry-uri',
            'Title Post',
            new \DateTime(),
            'entry',
            'The Content'
        );

        $this->defaultManager->persist($entry);
        $this->defaultManager->flush();

        $prompts = [
            'Roaster1' => 'You are an intellectual with a sharp wit...',
            'NiceGuy17' => 'You are Mr. Nice Guy, an intelligent and incredibly kind...'
        ];

        $commentWriter = Mockery::mock(CommentWriterInterface::class);
        $commentWriter->shouldReceive('write')
            ->andReturn('Generated comment content');

        $createAgentEntryComments = new CreateAgentEntryComments($registry, $prompts);

        $input = new ArrayInput([
            'entryId' => (string) $entry->getId()
        ], $createAgentEntryComments->getDefinition());

        $output = new BufferedOutput();
        $outputStyle = new OutputStyle(new StringInput(''), $output);

        $createAgentEntryComments->setInput($input);
        $createAgentEntryComments->setOutput($outputStyle);

        $createAgentEntryComments->handle($commentWriter);

        $commentsForEntry = $this->comments->findBy(['entryUri' => 'example-entry-uri']);
        $this->assertCount(2, $commentsForEntry, 'Entry should have exactly two comments. One for each prompt.');
    }

    /** @test */
    public function it_creates_agent_comments_for_entries_with_no_agent_comment()
    {
        $registry = $this->app->make('registry');
        $em = $this->defaultManager;
        $entries = $this->app->make(Entries::class);

        $entry1 = new ContentItem(
            'example-entry-uri-1',
            'Title Post 1',
            new \DateTime(),
            'entry',
            'The Content'
        );

        $entry2 = new ContentItem(
            'example-entry-uri-2',
            'Title Post 2',
            new \DateTime(),
            'entry',
            'The Content'
        );

        $em->persist($entry1);
        $em->persist($entry2);
        $em->flush();

        $comment = new Comment(
            'example-entry-uri-1',
            'NiceGuy17',
            'This is a generated comment'
        );

        $this->commentsManager->persist($comment);
        $this->commentsManager->flush();

        $prompts = [
            'Roaster1' => 'You are an intellectual with a sharp wit...',
            'NiceGuy17' => 'You are Mr. Nice Guy, an intelligent and incredibly kind...'
        ];

        $commentWriter = Mockery::mock(CommentWriterInterface::class);
        $commentWriter->shouldReceive('write')
            ->andReturn('Generated comment content');

        $createAgentEntryComments = new CreateAgentEntryComments($registry, $prompts, $entries);

        $input = new ArrayInput([], $createAgentEntryComments->getDefinition());

        $output = new BufferedOutput();
        $outputStyle = new OutputStyle(new StringInput(''), $output);

        $createAgentEntryComments->setInput($input);
        $createAgentEntryComments->setOutput($outputStyle);

        $createAgentEntryComments->handle($commentWriter);

        $commentsForEntry2 = $this->comments->findBy(['entryUri' => 'example-entry-uri-2']);
        $this->assertCount(2, $commentsForEntry2, 'Entry 2 should have exactly two comments. One for each prompt.');

        $commentsForEntry1 = $this->comments->findBy(['entryUri' => 'example-entry-uri-1']);
        $this->assertCount(1, $commentsForEntry1, 'Entry 1 should not have received a new comment.');
    }
}
