<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
/**
 * Post controller.
 *
 * 
 *@Route("{_locale}/post")
 * defaults={_locale": "fr"}
 */
class PostController extends Controller
{
    /**
     * Lists all post entities.
     *
     * @Route("/", name="post_index")
     * 
     * @Method("GET")
     */
    public function indexAction()
    {
       $em = $this->getDoctrine()->getManager();

      $posts = $em->getRepository('AppBundle:Post')->findBy(array(), array('created'=>'desc'));

       return $this->render('post/index.html.twig', array(
            'posts' => $posts,
        ));
    }

    /**
     * Creates a new post entity.
     *
     * @Route("/new/", name="post_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
      /*if($this->get('session')->get('userPermission') != 2)
      return $this->redirectToRoute('post_index');*/

        if ($request->getMethod() == 'POST') {
           $post = new Post();
           $post->setTitre($request->get('titre'));
           $post->setText($request->get('text'));
           $post->setUserId($this->get('session')->get('userId'));
           $post->setDraftCopy(1);
           $fileName = $this->get('app.article_uploader')->upload($request->files->get('img'));

           $post->setImg($fileName);

           $em = $this->getDoctrine()->getManager();
           $em->persist($post);
           $em->flush();

           $this->addFlash('success', 'L\'article a bien été ajouté !');
          return $this->redirectToRoute('post_index');

        }
        return $this->render('post/new.html.twig');
    }

    /**
     * Displays a form to edit an existing post entity.
     *
     * @Route("/{id}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Post $post)
    {
      if($this->get('session')->get('userPermission') != 2)
      return $this->redirectToRoute('post_index');

        if ($request->getMethod() == 'POST') {

            $em = $this->getDoctrine()->getManager();
            $post = $em->getRepository('AppBundle:Post')->find($post->getId());

            if (!$post)
            throw $this->createNotFoundException('Pas de post pour l\'id ' . $post->getId());

            $post->setText($request->get('text'));

            $fileName = $this->get('app.article_uploader')->upload($request->files->get('img'));
            if($fileName){
              @unlink('../web/uploads/'. $post->getImg());
              $post->setImg($fileName);
            }

            $post->fieldModified();

            $em->flush();


            $post->setText($request->get('text'));
	    $post->setTitre($request->get('titre'));
            $post->fieldModified();

            $em->flush();

            $this->addFlash('success', 'L\'article a bien été modifié !');

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/edit.html.twig', array(
            'post' => $post,
        ));
    }

    /**
     * Deletes a get entity.
     *
     *
     * @Route("/{id}/delete", name="post_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

      if($this->get('session')->get('userPermission') != 2)
      return $this->redirectToRoute('post_index');

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);
        
        if (!$post)
        throw $this->createNotFoundException('Pas d\'article pour l\'id ' . $id);

        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'L\'article a bien été supprimé !');
        return $this->redirectToRoute('post_index');
    }

    /**
     * Publish.
     *
     * @Route("/{id}/publish", name="post_publish")
     * @Method("GET")
     */
    public function publishAction(Request $request, $id)
    {
      /*if($this->get('session')->get('userPermission') != 2)
      return $this->redirectToRoute('post_index');*/

      $em = $this->getDoctrine()->getManager();
      $post = $em->getRepository('AppBundle:Post')->find($id);

      if (!$post)
      throw $this->createNotFoundException('Pas de post pour l\'id ' . $id);

      $post->setDraftCopy(0);
      $em->flush();

      $this->addFlash('success', 'L\'article a bien été publié !');
      return $this->redirectToRoute('post_index');
    }

    /**
     * hide.
     *
     * @Route("/{id}/hide", name="post_hide")
     * @Method("GET")
     */
    public function hideAction(Request $request, $id)
    {
      if($this->get('session')->get('userPermission') != 2)
      return $this->redirectToRoute('post_index');

      $em = $this->getDoctrine()->getManager();
      $post = $em->getRepository('AppBundle:Post')->find($id);

      if (!$post)
      throw $this->createNotFoundException('Pas de post pour l\'id ' . $id);

      $post->setDraftCopy(1);
      $em->flush();

      $this->addFlash('success', 'L\'article a bien été masqué !');
      return $this->redirectToRoute('post_index');
    }

    /**
     * Creates a form to delete a post entity.
     *
     * @param Post $post The post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
}
