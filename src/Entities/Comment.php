<?php

declare(strict_types=1);

namespace Skimpy\Comments\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="comments")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @phpstan-ignore-next-line
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Comment")
     * @ORM\JoinColumn(name="replies_to_id", referencedColumnName="id", nullable=true)
     */
    private ?Comment $repliesTo = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $entryUri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $author;

    /**
     * @ORM\Column(type="text")
     */
    private string $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    public function __construct(
        string $entryUri,
        string $author,
        string $content
    ) {
        $this->entryUri = $entryUri;
        $this->author = $author;
        $this->content = $content;
        $this->createdAt = new \DateTime();
    }

    public function repliesTo(): ?Comment
    {
        return $this->repliesTo;
    }

    public function setRepliesTo(?Comment $repliesTo): void
    {
        $this->repliesTo = $repliesTo;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function entryUri(): string
    {
        return $this->entryUri;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function createdAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}