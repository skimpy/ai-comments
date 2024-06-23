<?php

namespace Tests\Skimpy\Comments\AI;

use Mockery;
use Skimpy\CMS\ContentItem;
use Tests\Skimpy\Comments\TestCase;
use Illuminate\Console\OutputStyle;
use Skimpy\Comments\Entities\Comment;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Skimpy\Comments\AI\CommentWriterInterface;
use Skimpy\Comments\AI\CreateAgentCommentReplies;

class CreateAgentCommentRepliesTest extends TestCase
{
    /** @test */
    public function it_creates_agent_comments_for_a_specified_comment_id()
    {
        $entry = new ContentItem(
            'the-uri',
            'The Title',
            new \DateTime(),
            'entry',
            'The Content'
        );

        $comment = new Comment(
            'the-uri',
            'NiceGuy17',
            'This is a generated comment'
        );

        $this->defaultManager->persist($entry);
        $this->defaultManager->flush();
        $this->commentsManager->persist($comment);
        $this->commentsManager->flush();

        $prompts = [
            'Roaster1' => 'You are an intellectual with a sharp wit...',
            'NiceGuy17' => 'You are Mr. Nice Guy, an intelligent and incredibly kind...'
        ];

        $commentWriter = Mockery::mock(CommentWriterInterface::class);
        $commentWriter->shouldReceive('write')
            ->andReturn('Generated comment content');

        $createAgentComments = new CreateAgentCommentReplies(app('registry'), $prompts);

        $input = new ArrayInput([
            'commentId' => (string) $comment->id()
        ], $createAgentComments->getDefinition());

        $output = new BufferedOutput();
        $outputStyle = new OutputStyle(new StringInput(''), $output);

        $createAgentComments->setInput($input);
        $createAgentComments->setOutput($outputStyle);

        $createAgentComments->handle($commentWriter);

        $reply = $this->comments->findOneBy(['repliesTo' => $comment]);

        $this->assertNotEmpty($reply, 'The comment should have replies.');
        $this->assertEquals($comment->id(), $reply->repliesTo()->id(), 'The reply should be linked to the original comment.');
        $this->assertContains($reply->author(), array_keys($prompts), 'The reply author should be either "Roast" or "nice_guy".');
    }

    /** @test */
    public function it_creates_agent_comments_for_recent_comments()
    {
        $entry = new ContentItem(
            'recent-uri',
            'Recent Title',
            new \DateTime(),
            'entry',
            'Recent Content'
        );

        $longComment = new Comment(
            'recent-uri',
            'RecentUser',
            'This is a recent comment. This is a recent comment. This is a recent comment. The comment has to be at least 260 characters to receive replies. Which is why we have all this text to be here to receive our reply. This is a an example comment. This is an example comments.'
        );

        $shortComment = new Comment(
            'recent-uri',
            'RecentUser',
            'This is a short comment.'
        );

        $oldComment = new Comment(
            'recent-uri',
            'OldUser',
            'This is an old comment that should not receive a reply.'
        );

        $oldComment->setCreatedAt((new \DateTime())->modify('-15 minutes'));

        $this->defaultManager->persist($entry);
        $this->defaultManager->flush();
        $this->commentsManager->persist($longComment);
        $this->commentsManager->persist($shortComment);
        $this->commentsManager->persist($oldComment);
        $this->commentsManager->flush();

        $prompts = [
            'Roaster1' => 'You are an intellectual with a sharp wit...',
            'NiceGuy17' => 'You are Mr. Nice Guy, an intelligent and incredibly kind...'
        ];

        $commentWriter = Mockery::mock(CommentWriterInterface::class);
        $commentWriter->shouldReceive('write')
            ->andReturn('Generated comment content');

        $createAgentComments = new CreateAgentCommentReplies(app('registry'), $prompts);

        $input = new ArrayInput([], $createAgentComments->getDefinition());

        $output = new BufferedOutput();
        $outputStyle = new OutputStyle(new StringInput(''), $output);

        $createAgentComments->setInput($input);
        $createAgentComments->setOutput($outputStyle);

        $createAgentComments->handle($commentWriter);

        $longCommentReply = $this->comments->findOneBy(['repliesTo' => $longComment]);
        $shortCommentReply = $this->comments->findOneBy(['repliesTo' => $shortComment]);
        $oldCommentReply = $this->comments->findOneBy(['repliesTo' => $oldComment]);

        $this->assertNotEmpty($longCommentReply, 'The long comment should have replies.');
        $this->assertEquals($longComment->id(), $longCommentReply->repliesTo()->id(), 'The reply should be linked to the long comment.');
        $this->assertContains($longCommentReply->author(), array_keys($prompts), 'The reply author should be either "Roast" or "nice_guy".');

        $this->assertEmpty($shortCommentReply, 'The short comment should not have replies.');
        $this->assertEmpty($oldCommentReply, 'The old comment should not have replies.');
    }
}