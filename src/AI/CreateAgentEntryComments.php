<?php

declare(strict_types=1);

namespace Skimpy\Comments\AI;

use Skimpy\CMS\ContentItem;
use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use Doctrine\ORM\EntityRepository;
use Skimpy\Comments\Entities\Comment;
use Skimpy\Comments\AI\CommentWriterInterface;
use LaravelDoctrine\ORM\IlluminateRegistry as Registry;

/**
 * Class CreateAgentEntryComments
 *
 * This command class is responsible for generating AI-based comments for entries.
 * It uses a specified set of prompts to generate responses to new entries or a specific entry.
 * The class interacts with the Doctrine ORM to manage comment entities and uses a CommentWriterInterface
 * implementation to generate the content of the comments.
 *
 * The command can be executed with an optional entryId argument to target a specific entry.
 * If no entryId is provided, it will respond to a random number of new entries that meet certain criteria.
 */
class CreateAgentEntryComments extends Command
{
    protected $signature = 'comments:create-agent-entry-comments {entryId?}';
    protected $description = 'Respond to entries with AI';

    private EntityManager $commentsManager;
    private EntityRepository $comments;

    /**
     * @var string[]
     */
    private array $prompts;

    private EntityRepository $entries;

    /**
     * @param string[] $prompts
     */
    public function __construct(Registry $registry, array $prompts)
    {
        parent::__construct();

        /** @var EntityManager $commentsManager */
        $commentsManager = $registry->getManager('comments');

        $this->commentsManager = $commentsManager;

        /** @phpstan-ignore-next-line */
        $this->entries = $registry->getRepository(ContentItem::class);
        $this->comments = $commentsManager->getRepository(Comment::class);

        $this->prompts = $prompts;
    }

    public function handle(CommentWriterInterface $writer): void
    {
        $entryId = $this->argument('entryId');
        $entries = $entryId ? [$this->singleEntry($entryId)] : $this->entriesForComment();

        foreach ($this->prompts as $promptCharacter => $promptContent) {
            $this->writeComment($entries, $writer, $promptCharacter, $promptContent);
        }
    }

    /**
     * @param ContentItem[] $entries
     */
    private function writeComment(
        array $entries,
        CommentWriterInterface $writer,
        string $promptCharacter,
        string $promptContent
    ): void {
        foreach ($entries as $entry) {
            $commentContent = $writer->write($promptContent, $entry->getContent());
            $comment = new Comment($entry->getUri(), $promptCharacter, $commentContent);

            $this->commentsManager->persist($comment);
            $this->commentsManager->flush();
        }
    }

    /**
     * @return ContentItem[]
     */
    private function entriesForComment(): array
    {
        $excludeEntries = $this->entryUrisWithComments();

        return $this->entries->createQueryBuilder('e')
            ->where('e.uri NOT IN (:excludeEntries)')
            ->setParameter('excludeEntries', $excludeEntries)
            ->getQuery()
            ->getResult();
    }

    private function singleEntry(string $entryId): ContentItem
    {
        $entry = $this->entries->findOneBy(['id' => $entryId]);

        if (!$entry) {
            throw new \RuntimeException("Entry with ID $entryId not found");
        }

        return $entry;
    }

    /**
     * The URIs of entries that already have a bot comment based on the entry content
     *
     * @return string[]
     */
    private function entryUrisWithComments(): array
    {
        $result = $this->comments->createQueryBuilder('c')
            ->select('c.entryUri')
            ->distinct()
            ->where('c.author IN (:ignoredAuthors)')
            ->setParameter('ignoredAuthors', $this->ignoredAuthors())
            ->getQuery()
            ->getResult();

        return array_column($result, 'entryUri');
    }

    /**
     * @return string[]
     */
    private function ignoredAuthors(): array
    {
        return array_keys($this->prompts);
    }
}