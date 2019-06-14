<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User implements UserInterface
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->emailNotifyPermission = true;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150, nullable=true, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=150, nullable=true, unique=true)
     * Per push notification mobile. Attualmente non è gestito ma solo previsto questo metodo.
     * Per un'ottimizzazione questo dato dovrebbe essere memorizzato su una entità relazionata N-1 con User, che tenga conto di più device per lo stesso utente.
     */
    private $deviceRegistrationId = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(
     *     min=8,
     *     minMessage="La password deve essere di almeno {{ limit }} caratteri"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=50, nullable=true, unique=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="boolean", nullable=true);
     */
    private $smsNotifyPermission = false;

    /**
     * @ORM\Column(type="boolean", nullable=true);
     */
    private $emailNotifyPermission = false;

    /**
     * @ORM\Column(type="boolean", nullable=true);
     */
    private $pushNotifyPermission = false;

    /**
    * @ORM\Column(type="string", nullable=true, unique=true)
    */
    private $apiToken;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default":null})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default":null})
     */
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getDeviceRegistrationId(): ?string
    {
        return $this->deviceRegistrationId;
    }

    /**
     * @param mixed $deviceRegistrationId
     */
    public function setDeviceRegistrationId($deviceRegistrationId): void
    {
        $this->deviceRegistrationId = $deviceRegistrationId;
    }

    /**
     * @return mixed
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getSmsNotifyPermission(): bool
    {
        return $this->smsNotifyPermission;
    }

    /**
     * @param mixed $smsNotifyPermission
     */
    public function setSmsNotifyPermission($smsNotifyPermission): void
    {
        $this->smsNotifyPermission = $smsNotifyPermission;
    }

    /**
     * @return mixed
     */
    public function getEmailNotifyPermission(): bool
    {
        return $this->emailNotifyPermission;
    }

    /**
     * @param mixed $emailNotifyPermission
     */
    public function setEmailNotifyPermission($emailNotifyPermission): void
    {
        $this->emailNotifyPermission = $emailNotifyPermission;
    }

    /**
     * @return mixed
     */
    public function getPushNotifyPermission()
    {
        return $this->pushNotifyPermission;
    }

    /**
     * @param mixed $pushNotifyPermission
     */
    public function setPushNotifyPermission($pushNotifyPermission): void
    {
        $this->pushNotifyPermission = $pushNotifyPermission;
    }

    /**
     * @return mixed
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    /**
     * @param mixed $apiToken
     */
    public function setApiToken($apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    private $role = '';
    public function getRole(): string
    {
        return count($this->roles)>0 ? $this->roles[0] : '';
    }

    public function setRole($role): void
    {
        $arr = [];
        $arr[] = $role;
        $this->setRoles($arr);
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): int
    {
        return 13;
    }

    /**
     * @return mixed
     */
    public function getUsername(): ?string
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {}
}
