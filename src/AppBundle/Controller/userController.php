<?php

namespace AppBundle\Controller;

use AppBundle\Entity\user;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 * @Route("user")
 */
class userController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:user')->findAll();

        return $this->render('user/index.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
      if ($request->getMethod() == 'POST') {
          if($request->get('password') == $request->get('repassword')){
            $user = new User();
            $user->setName($request->get('pseudo'));
            $user->setEmail($request->get('email'));
            $user->setPassword($request->get('password'));
            $user->setAvatar('');

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('post_index');
          }
        }

        return $this->render('user/new.html.twig');
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     */
    public function showAction(user $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return $this->render('user/show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, user $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        if ($request->getMethod() == 'POST') {
            if($request->get('password') == $request->get('repassword')){
              $em = $this->getDoctrine()->getManager();
              $user = $em->getRepository('AppBundle:user')->find($user->getId());

              if (!$user)
              throw $this->createNotFoundException('Pas de utilisateur pour l\'id ' . $user->getId());
              ;
              $user->setName($request->get('pseudo'));
              $user->setEmail($request->get('email'));
              $user->setPassword($request->get('password'));
              $user->setAvatar('');

              $em->flush();

              return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a user entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, user $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param user $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(user $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
