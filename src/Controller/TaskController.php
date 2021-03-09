<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\MailType;
use App\Form\TaskType;
use Symfony\Component\Mime\Email;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{

    /**
     * @var TaskRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructeur du TaskController pour injection de dépendances
     *
     * @param TaskRepository $repository
     * @param EntityManagerInterface $manager
     */
    public function __construct(TaskRepository $repository, EntityManagerInterface $manager, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->translator = $translator;
    }

    /**
     * @Route("/tasks/listing", name="tasks_listing")
     */
    public function taskListing(): Response
    {
        // Récuperer les infos du user connecté
        $user = $this->getUser();
        if ($user->getRoles()[0] === 'ROLE_ADMIN') {
            // Recuperer les données du repository pour l'admin
            $tasks = $this->repository->findAll();
        } else {
            // Recuperer les données du repository pour le user connecté
            $tasks = $this->repository->findBy(
                ['user' => $user->getId()],
                ['id' => 'ASC']
            );
        }
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }


    /**
     * @Route("/tasks/create", name="task_create")
     * @Route("/tasks/update/{id}", name="task_update", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @return Response
     */
    public function task(Task $task = null, Request $request): Response
    {
        // Récuperer les infos du user connecté
        $user = $this->getUser();

        if (!$task) {
            $task = new Task();
            $flag = true;
        } else {
            $flag = false;
        }

        $form = $this->createForm(TaskType::class, $task, []);

        if ($flag) {
            $task->setCreatedAt(new \DateTime());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData())
                ->setUser($user);

            $this->manager->persist($task);
            $this->manager->flush();

            $this->addFlash('success', $flag ? "Votre tâche a bien été ajouté" : "Votre tache à bien été modifiée");
            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }


    /**
     * @Route("/tasks/delete/{id}", name="task_delete", requirements={"id"="\d+"})
     *
     * @param Task $task
     * @return Response
     */
    public function delete(Task $task): Response
    {
        $this->manager->remove($task);
        $this->manager->flush();

        $this->addFlash('warning', 'Votre tâche à bien été supprimée');
        return $this->redirectToRoute('tasks_listing');
    }

    /**
     * définir le chemin des données
     * @Route("/tasks/detail/{id}", name="task_detail", requirements={"id"="\d+"})
     * @param Task $task
     * @return Response
     */
    public function show(Task $task): Response
    {
        return $this->render('task/detail.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @Route (" /tasks/email/{id}", name="task_email", requirements={"id"="\d+"}))
     *
     * @param Request $request
     * @param Task $task
     * @param MailerInterface $mailer
     * @return Response
     */
    public function sendEmail(Request $request, Task $task, MailerInterface $mailer): Response
    {

        $user = $this->getUser()->getEmail();
        $sub = "Vous avez reçu la tache: " . $task->getName();
        $text = "Son contenu est: " . $task->getDescription() . "\n" .
            "Date de début: " . $task->getCreatedAt()->format('d-m-Y') . "\n" .
            "Date de fin: " . $task->getDueAt()->format('d-m-Y') . "\n";

        //Creation du formulaire
        $form = $this->createForm(
            MailType::class,
            ['from' => $user, 'name' => $sub, 'description' => $text]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $emailDest = $form['to']->getData();
            $message = (new Email())
                ->to($emailDest)
                ->subject($sub)
                ->text($text);
            $mailer->send($message);

            $this->addFlash('success', $this->translator->trans('flash.mail.success'));
            return $this->redirectToRoute('tasks_listing');
        }
        return $this->render('email/task.html.twig', ['form' => $form->createView()]);
    }
}
