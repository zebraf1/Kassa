<?php

namespace App\Entity;

use App\Repository\MemberStatusCreditLimitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberStatusCreditLimitRepository::class)]
#[ORM\Table(name: 'ollekassa_staatused_credit_limit')]
class MemberStatusCreditLimit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MemberStatus $status = null;

    #[ORM\Column]
    private ?int $creditLimit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?MemberStatus
    {
        return $this->status;
    }

    public function setStatus(?MemberStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreditLimit(): ?int
    {
        return $this->creditLimit;
    }

    public function setCreditLimit(int $creditLimit): static
    {
        $this->creditLimit = $creditLimit;

        return $this;
    }
}
