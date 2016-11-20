<?php

namespace FirstBundle\Controller;

use FirstBundle\Entity\Reminder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reminder controller.
 *
 */
class ReminderController extends Controller
{

    public function displayAction($workset_id){

        $user_id = 1;

        $em = $this->getDoctrine()->getManager();

        $reminderDao = $em->getRepository('FirstBundle:Reminder');

//        $reminderDao->createReminderSet($user_id, $workset_id);

        $rmlist = $reminderDao->findBy(array('userId' => $user_id, 'worksetId' => $workset_id));

        dump($rmlist);die;

        return $this->render('FirstBundle:Reminder:display.html.twig', array(
            'reminders' => array(),
        ));
    }

    /**
     * Lists all reminder entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reminders = $em->getRepository('FirstBundle:Reminder')->findAll();

//        dump($reminders);die;

        return $this->render('FirstBundle:Reminder:index.html.twig', array(
            'reminders' => $reminders,
        ));
    }

    /**
     * Creates a new reminder entity.
     *
     */
    public function newAction(Request $request)
    {
        $reminder = new Reminder();
        $form = $this->createForm('FirstBundle\Form\ReminderType', $reminder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reminder);
            $em->flush($reminder);

            return $this->redirectToRoute('reminder_show', array('id' => $reminder->getId()));
        }

        return $this->render('FirstBundle:Reminder:new.html.twig', array(
            'reminder' => $reminder,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a reminder entity.
     *
     */
    public function showAction(Reminder $reminder)
    {
        $deleteForm = $this->createDeleteForm($reminder);

        return $this->render('reminder/show.html.twig', array(
            'reminder' => $reminder,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing reminder entity.
     *
     */
    public function editAction(Request $request, Reminder $reminder)
    {
        $deleteForm = $this->createDeleteForm($reminder);
        $editForm = $this->createForm('FirstBundle\Form\ReminderType', $reminder);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reminder_edit', array('id' => $reminder->getId()));
        }

        return $this->render('FirstBundle:Reminder:edit.html.twig', array(
            'reminder' => $reminder,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a reminder entity.
     *
     */
    public function deleteAction(Request $request, Reminder $reminder)
    {
        $form = $this->createDeleteForm($reminder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($reminder);
            $em->flush($reminder);
        }

        return $this->redirectToRoute('reminder_index');
    }

    /**
     * Creates a form to delete a reminder entity.
     *
     * @param Reminder $reminder The reminder entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Reminder $reminder)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reminder_delete', array('id' => $reminder->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
