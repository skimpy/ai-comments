<?php

declare(strict_types=1);

namespace Skimpy\Comments\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="emails")
 */
class Email implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @phpstan-ignore-next-line
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $token;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $entryUri;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $verifiedAt = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $expiresAt;

    public function __construct(string $name, string $email, string $entryUri)
    {
        $this->name = $name;
        $this->email = $email;
        $this->entryUri = $entryUri;
        $this->token = bin2hex(random_bytes(16));
        $this->createdAt = new \DateTime();
        $this->expiresAt = (new \DateTime())->modify('+3 days');
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function entryUri(): string
    {
        return $this->entryUri;
    }

    public function token(): ?string
    {
        return $this->token;
    }

    public function verifiedAt(): ?\DateTime
    {
        return $this->verifiedAt;
    }

    public function verify(): void
    {
        $this->verifiedAt = new \DateTime();
    }

    public function verified(): bool
    {
        return $this->verifiedAt instanceof \DateTime;
    }

    public function createdAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function resetToken(): void
    {
        $this->token = bin2hex(random_bytes(16));
        $this->expiresAt = (new \DateTime())->modify('+3 days');
    }

    public function expiration(): ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @return array{id: int, name: string, email: string, createdAt: string, expiresAt: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'expiresAt' => $this->expiresAt->format('Y-m-d H:i:s'),
        ];
    }
}