<?php

declare(strict_types=1);

namespace Skimpy\Comments\AI;

use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use Doctrine\ORM\EntityRepository;
use Skimpy\Comments\Entities\Comment;
use Skimpy\Comments\AI\CommentWriterInterface;
use LaravelDoctrine\ORM\IlluminateRegistry as Registry;

/**
 * This command class is responsible for generating AI-based replies to comments.
 * It uses a specified set of prompts to generate responses to new comments or a specific comment.
 * The class interacts with the Doctrine ORM to manage comment entities and uses a CommentWriterInterface
 * implementation to generate the content of the replies.
 *
 * The command can be executed with an optional commentId argument to target a specific comment.
 * If no commentId is provided, it will respond to a random number of new comments that meet certain criteria.
 */
class CreateAgentCommentReplies extends Command
{
    protected $signature = 'comments:create-agent-comment-replies {commentId?}';
    protected $description = 'Respond to a random number of new comments with AI';

    private EntityManager $commentsManager;
    private EntityRepository $comments;

    /** @var array<string, string> */
    private array $prompts;

    /**
     * @param array<string, string> $prompts
     */
    public function __construct(Registry $registry, array $prompts)
    {
        parent::__construct();

        /** @var EntityManager $commentsManager */
        $commentsManager = $registry->getManager('comments');

        $this->commentsManager = $commentsManager;
        $this->comments = $commentsManager->getRepository(Comment::class);
        $this->prompts = $prompts;
    }

    public function handle(CommentWriterInterface $commentWriter): void
    {
        $commentId = $this->argument('commentId');
        $promptCharacter = array_rand($this->prompts);
        $promptContent = $this->prompts[$promptCharacter];

        $commentsForReply = $commentId
            ? [$this->singleComment($commentId)]
            : $this->commentsForReply();

        foreach ($commentsForReply as $comment) {

            $replyContent = $commentWriter->write($promptContent, $comment->content());

            $reply = new Comment($comment->entryUri(), $promptCharacter, $replyContent);

            $reply->setRepliesTo($comment);

            $this->commentsManager->persist($reply);
        }

        $this->commentsManager->flush();
    }

    /**
     * @return Comment[]
     */
    private function commentsForReply(): array
    {
        return $this->comments->createQueryBuilder('c')
            ->where('c.author NOT IN (:ignoredAuthors)')
            ->andWhere('c.repliesTo IS NULL')
            ->andWhere('c.createdAt >= :timeAgo')
            ->andWhere('LENGTH(c.content) >= 260')
            ->andWhere('(SELECT COUNT(r.id) FROM ' . Comment::class . ' r WHERE r.repliesTo = c.id) <= 30')
            ->setParameter('ignoredAuthors', $this->ignoredAuthors())
            ->setParameter('timeAgo', new \DateTime('-10 minutes'))
            ->getQuery()
            ->getResult();
    }

    private function singleComment(string $commentId): Comment
    {
        $comment = $this->comments->findOneBy(['id' => $commentId]);

        if (!$comment) {
            throw new \RuntimeException("Comment with ID $commentId not found");
        }

        return $comment;
    }

    /**
     * @return string[]
     */
    private function ignoredAuthors(): array
    {
        return array_keys($this->prompts);
    }
}