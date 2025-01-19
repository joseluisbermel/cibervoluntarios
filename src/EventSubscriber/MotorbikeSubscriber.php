<?php

namespace App\EventSubscriber;

use App\Entity\Motorbike;
use App\Repository\CustomerRepository;
use App\Repository\MotorbikeRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MotorbikeSubscriber implements EventSubscriber
{
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @var CustomerRepository
     */
    private CustomerRepository $customerRepository;

    /**
     * @var MotorbikeRepository
     */
    private MotorbikeRepository $motorbikeRepository;

    /**
     * MotorbikeSubscriber
     * @param MailerInterface $mailer
     * @param CustomerRepository $customerRepository
     * @param MotorbikeRepository $motorbikeRepository
     */
    public function __construct(MailerInterface $mailer, CustomerRepository $customerRepository, MotorbikeRepository $motorbikeRepository)
    {
        $this->mailer = $mailer;
        $this->customerRepository = $customerRepository;
        $this->motorbikeRepository = $motorbikeRepository;
    }

    /**
     * List subscribed events
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate
        ];
    }

    /**
     * Pre Persist
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Motorbike) {
            return;
        }

        if($entity->getLimitedEdition()) {
            $limitedEditionMotorbikes = $this->motorbikeRepository->findLimitedEdition();

            if (count($limitedEditionMotorbikes) >= 10) {
                $oldestLimitedEdition = $this->findOldestMotorbike($limitedEditionMotorbikes);
                $oldestLimitedEdition->setLimitedEdition(false);

                $this->motorbikeRepository->save($oldestLimitedEdition);
            }
        }
    }

    /**
     * Post Persist
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Motorbike) {
            return;
        }

        if ($entity->getType() === 'Classic') {
            $this->sendNotificationEmail($entity);
        }
    }

    /**
     * Pre Update
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        
        if (!$entity instanceof Motorbike) {
            return;
        }

        $entity->setUpdatedAt(new \DateTimeImmutable());
    }

    /**
     * Send email
     * @param Motorbike $motorbike
     * @return void
     */
    private function sendNotificationEmail(Motorbike $motorbike): void
    {
        $toEmails = $this->getCustomersEmails();

        foreach ($toEmails as $toEmail) {
            $email = (new Email())
                ->to(trim($toEmail))
                ->subject('New Classic bike available')
                ->text(sprintf(
                    'Hello, a new Classic type motorcycle has been added: %s (Brand: %s)',
                    $motorbike->getModel(),
                    $motorbike->getBrand()
                ));

            $this->mailer->send($email);
        }
    }

    /**
     * Get customers subscribed
     * @return array
     */
    private function getCustomersEmails(): array
    {
        $customers = $this->customerRepository->findBy(['isSubscribed' => true]);

        return array_map(static fn($customer) => $customer->getEmail(), $customers);
    }

    /**
     * Get old motorbike
     * @param array $motorbikes
     * @param Motorbike $a
     * @param Motorbike $b
     * @return Motorbike
     */
    private function findOldestMotorbike(array $motorbikes): Motorbike
    {
        usort($motorbikes, fn(Motorbike $a, Motorbike $b) => $a->getCreatedAt() <=> $b->getCreatedAt());
        return $motorbikes[0];
    }
}
