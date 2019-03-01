<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message= "L'email que vous avez indiqué est déja utilisé",
 *     )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     *
     */
    private $email;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Les deux champs doivent être identiques")
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="6", minMessage="C'est bien trop court...même si c'est pas la taille qui compte bien sûr ;)")"
     * @Assert\EqualTo(propertyPath="confirm_password", message="Les deux champs doivent être identiques")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $request;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $open;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="smallint")
     */
    private $profil;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    public function getRoles(){
        return ['ROLE_USER'];
    }


    public function getSalt(){}


    public function eraseCredentials(){}

    public function getRequest(): ?\DateTimeInterface
    {
        return $this->request;
    }

    public function setRequest(?\DateTimeInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getOpen(): ?int
    {
        return $this->open;
    }

    public function setOpen(?int $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getProfil(): ?int
    {
        return $this->profil;
    }

    public function setProfil(int $profil): self
    {
        $this->profil = $profil;

        return $this;
    }
}
