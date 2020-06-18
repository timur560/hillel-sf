<?php


namespace App\Controller;


use App\Entity\Post;
use App\Entity\ShortLink;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WallController extends AbstractController
{
    /**
     * @Route("/wall", name="posts_list")
     */
    public function postsList(Request $request)
    {
        $form = $this->createForm(PostType::class, new Post());

        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Post $post */
            $post = $form->getData();
            $post->setUser($user);

            // todo: short links in content

            // 1. find all matches

            preg_match_all('/http[s]?:\/\/[\w\.\/%-]+/', $post->getContent(), $longLinks);

            // 2. create short links
            // 3. replace with short links

            foreach ($longLinks[0] as $longLink) {
                $shortLink = new ShortLink();
                $shortLink->setFullUrl($longLink);
                $shortLink->setShortCode($this->generateRandomString());

                $em->persist($shortLink);

                $post->setContent(
                    str_replace(
                        $longLink,
                        '<a href="http://localhost:8000/r/' . $shortLink->getShortCode().'">http://localhost:8000/r/' . $shortLink->getShortCode() . '</a>',
                        $post->getContent()
                    )
                );
            }

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('posts_list');
        }

        return $this->render('wall/posts-list.html.twig', [
            'posts' => $user->getPosts(),
            'form' => $form->createView(),
        ]);
    }

    private function generateRandomString($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}