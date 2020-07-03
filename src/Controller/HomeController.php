<?php


namespace App\Controller;


use App\Entity\Statistic;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @return Response
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/top_links")
     */
    public function topLinks()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        $result = $qb
            ->select('count(s) as qty')
            ->addSelect('l.fullUrl')
            ->from(Statistic::class, 's')
            ->join('s.link', 'l')
            ->groupBy('s.link')
            ->orderBy('qty', 'asc')
            ->getQuery()
            ->execute();

        return $this->render('home/top_link.html.twig', [
            'links' => $result,
        ]);
    }
}