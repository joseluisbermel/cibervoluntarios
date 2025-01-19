<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Customer;
use App\Entity\Motorbike;
use App\EventSubscriber\MotorbikeSubscriber;
use App\Repository\CustomerRepository;
use App\Repository\MotorbikeRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MotorbikeSubscriberTest extends TestCase
{
    private $mailerMock;
    private $customerRepositoryMock;
    private $motorbikeRepositoryMock;
    private $subscriber;

    protected function setUp(): void
    {
        $this->mailerMock = $this->createMock(MailerInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $this->motorbikeRepositoryMock = $this->createMock(MotorbikeRepository::class);

        $this->subscriber = new MotorbikeSubscriber(
            $this->mailerMock,
            $this->customerRepositoryMock,
            $this->motorbikeRepositoryMock
        );
    }

    public function testPrePersistLimitedEdition(): void
    {
        $motorbike = new Motorbike();
        $motorbike->setLimitedEdition(true);

        $limitedEditionMotorbikes = [
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike()),
            (new Motorbike())
        ];
        
        $this->motorbikeRepositoryMock
            ->expects($this->once())
            ->method('findLimitedEdition')
            ->willReturn($limitedEditionMotorbikes);

        $this->motorbikeRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Motorbike $motorbike) {
                return !$motorbike->getLimitedEdition();
            }));

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($motorbike);

        $this->subscriber->prePersist($args);
    }

    public function testPostPersistClassicMotorbikeSendsEmail(): void
    {
        $motorbike = new Motorbike();
        $motorbike->setType('Classic');
        $motorbike->setModel('Model X');
        $motorbike->setBrand('Brand Y');

        $customers = [
            (new Customer())->setEmail('customer1@example.com'),
            (new Customer())->setEmail('customer2@example.com')
        ];

        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['isSubscribed' => true])
            ->willReturn($customers);

        $this->mailerMock
            ->expects($this->exactly(2))
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return $email->getTo()[0]->getAddress() === 'customer1@example.com' ||
                       $email->getTo()[0]->getAddress() === 'customer2@example.com';
            }));

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($motorbike);

        $this->subscriber->postPersist($args);
    }

    public function testPreUpdateSetsUpdatedAt(): void
    {
        $motorbike = new Motorbike();

        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($motorbike);

        $this->subscriber->preUpdate($args);

        $this->assertInstanceOf(\DateTimeImmutable::class, $motorbike->getUpdatedAt());
    }

    public function testPrePersistNonMotorbikeEntity(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn(new \stdClass());

        $this->motorbikeRepositoryMock->expects($this->never())->method('findLimitedEdition');
        $this->subscriber->prePersist($args);
    }

    public function testPostPersistNonMotorbikeEntity(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn(new \stdClass());

        $this->mailerMock->expects($this->never())->method('send');
        $this->subscriber->postPersist($args);
    }

    public function testPreUpdateNonMotorbikeEntity(): void
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn(new \stdClass());

        $this->subscriber->preUpdate($args);
        // No exception or action should occur for non-Motorbike entities.
        $this->assertTrue(true);
    }
}
