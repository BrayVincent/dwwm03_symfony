<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/listing", name="tasks_listing")
     */
    public function taskListing(): Response
    {

        // On va chercher par Doctrine le repository de nos Task
        $repository = $this->getDoctrine()->getRepository(Task::class);

        // dans ce repository nous récupérons toutes les données
        $tasks = $repository->findAll();

        // affichage des données dans le var_dumper
        // dd($tasks);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }


    /**
     * @Route("/tasks/create", name="task_create")
     *
     * @param Request $request
     * @return Response
     */
    public function createTask(Request $request): Response
    {

        // On crée un nouvel objet Task
        $task = new Task;

        // On nourri notre objet Task avec nos données calculées
        $task->setCreatedAt(new \DateTime());

        $form = $this->createForm(TaskType::class, $task, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($task);
            $manager->flush();

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/update/{id}", name="task_update", requirements={"id"="\d+"})
     *
     * @param [type] $id
     * @param Request $request
     * @return Response
     */
    public function updateTask($id, Request $request): Response
    {

        $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $id]);

        $form = $this->createForm(TaskType::class, $task, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $task->setName($form['name']->getData())
                ->setDescription($form['description']->getData())
                ->setDueAt($form['dueAt']->getData())
                ->setTag($form['tag']->getData());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($task);
            $manager->flush();

            return $this->redirectToRoute('tasks_listing');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView(),
            'task' => $task
        ]);
    }
}
