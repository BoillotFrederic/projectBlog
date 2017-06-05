<?php

namespace AppBundle\Controller;

use AppBundle\Entity\user;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
>>>>>>> f9ae39f506f10601fa4803ee486f9ce805e2c0c3

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
            $user->setPermission(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            $this->addFlash('success', 'L\'utilisateur a bien été créé !');
            return $this->redirectToRoute('post_index');
          }
        }

        return $this->render('user/new.html.twig');
    }

    /**
     * Connexion
     *
     * @Route("/connect", name="user_connect")
     * @Method("POST")
     */
    public function connect(Request $request)
    {
      if ($request->getMethod() == 'POST') {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:user')->findBy(array('name' => $request->get('pseudo')));

        if(isset($user[0]) && $user[0]->getPassword() == md5($request->get('password'))){
          $this->get('session')->set('connected', true);

          $this->get('session')->set('userId', $user[0]->getId());
          $this->get('session')->set('userName', $user[0]->getName());
          $this->get('session')->set('userAvatar', $user[0]->getAvatar());
          $this->get('session')->set('userPermission', $user[0]->getPermission());

          $this->addFlash('success', 'Bonjour ' . $user[0]->getName() . ', Vous êtes connecté !');
          return $this->redirectToRoute('post_index');
        }
        else{
          $this->addFlash('error', 'La connexion a échoué !');
          return $this->redirectToRoute('post_index');
        }
      }
    }

    /**
     * Disconnect
     *
     * @Route("/disconnect", name="user_disconnect")
     * @Method("GET")
     */
    public function disconnect()
    {
      $this->get('session')->set('connected', false);
      $this->get('session')->set('userPermission', 0);
      $this->addFlash('success', 'Vous avez été deconnecté !');
      return $this->redirectToRoute('post_index');
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     */
    public function showAction(user $user)
    {
      if(!$this->get('session')->get('connected'))
      return $this->redirectToRoute('user_index');

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
      if(!$this->get('session')->get('connected'))
      return $this->redirectToRoute('user_index');

      $em = $this->getDoctrine()->getManager();
      $user = $em->getRepository('AppBundle:user')->find($user->getId());

      if($user->getId() != $this->get('session')->get('userId') &&
         $this->get('session')->get('userPermission') == 1)
      return $this->redirectToRoute('post_index');

        $deleteForm = $this->createDeleteForm($user);

        if ($request->getMethod() == 'POST') {
            if($request->get('password') == $request->get('repassword')){

              if (!$user)
              throw $this->createNotFoundException('Pas de utilisateur pour l\'id ' . $user->getId());

              $user->setEmail($request->get('email'));

              if($this->get('session')->get('userPermission') == 2)
              $user->setName($request->get('pseudo'));

              if($request->get('password'))
              $user->setPassword($request->get('password'));

              $fileName = $this->get('app.article_uploader')->upload($request->files->get('avatar'));
              if($fileName){
                @unlink('../web/uploads/'. $user->getAvatar());
                $user->setAvatar($fileName);
                $this->get('session')->set('userAvatar', $fileName);
              }

              $em->flush();

              $this->addFlash('success', 'L\'utilisateur a bien été mise à jour !');

              if($this->get('session')->get('userPermission') == 2)
              return $this->redirectToRoute('user_index');
              else
              return $this->redirectToRoute('post_index');
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
      if(!$this->get('session')->get('userPermission') != 2)
      return $this->redirectToRoute('post_index');

        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été supprimé !');
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
