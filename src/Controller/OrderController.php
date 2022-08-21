<?php

namespace App\Controller;

use App\Service\OrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Require FULLY AUTHENTICATED USER for all actions of this controller
 * @IsGranted("IS_AITHENTICATED_FULLY")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/order", name="app_order")
     */
    public function index(OrderService $orderService): Response
    {
        $orderItems = $orderService->getFullOrder();

        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
            'items' => $orderItems,
        ]);
    }

    /**
     * @Route("/order/add/{id}", name="add_order")
     */
    public function add(OrderService $orderService, int $id)
    {
        $orderService->add($id);

        return $this->redirectToRoute('app_product');
    }

    /**
     * @Route("/order/delete/{id}", name="delete_order")
     */
    public function delete(OrderService $orderService, int $id)
    {
        $orderService->delete($id);

        return $this->redirectToRoute('app_order');
    }
}
