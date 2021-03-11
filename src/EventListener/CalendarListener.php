<?php

namespace App\EventListener;

use App\Entity\Task;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarListener
{
    private $manager;
    private $router;

    public function __construct(
        EntityManagerInterface $manager,
        UrlGeneratorInterface $router
    ) {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * Load all tasks in calendar
     *
     * @param CalendarEvent $calendar
     * @return void
     */
    public function load(CalendarEvent $calendar): void
    {
        $taskRepository = $this->manager->getRepository(Task::class);
        $tasks = $taskRepository->findAll();

        foreach ($tasks as $task) {
            $taskEvent = new Event(
                $task->getName(),
                $task->getBeginAt(),
                $task->getEndAt()
            );

            $taskEvent->setOptions([
                'backgroundColor' => '#e95420',
                'borderColor' => '#e95420',
                'id' => $task->getId(),
            ]);

            $taskEvent->addOption('title', $task->getName());
            $taskEvent->addOption('beginAt', $task->getBeginAt());
            $taskEvent->addOption('endAt', $task->getEndAt());
            $taskEvent->addOption('fulltext', $task->getDescription());

            $calendar->addEvent($taskEvent);
        }
    }
}
